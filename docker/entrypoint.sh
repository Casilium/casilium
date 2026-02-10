#!/usr/bin/env bash
set -euo pipefail

# Ensure Doctrine proxy/cache directories are writable by www-data
mkdir -p data/cache/DoctrineEntityProxy
chown -R www-data:www-data data/cache
git config --global --add safe.directory /var/www/html || true

DB_HOST="${DB_HOST:-db}"
DB_NAME="${DB_NAME:-casilium}"
DB_USER="${DB_USER:-casilium}"
DB_PASSWORD="${DB_PASSWORD:-casilium_password}"
MFA_ISSUER="${MFA_ISSUER:-casilium.local}"
APP_URL="${APP_URL:-http://localhost:8080}"

# Backward compatibility: older merges may have written mysql:// which DBAL no longer accepts.
if [[ -f config/autoload/local.php ]]; then
    sed -i "s|'url' => 'mysql://|'url' => 'pdo-mysql://|g" config/autoload/local.php
fi

if [[ ! -f config/autoload/local.php ]]; then
    if [[ -z "${ENCRYPTION_KEY:-}" ]]; then
        ENCRYPTION_KEY="$(php -r 'echo sodium_bin2hex(random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES));')"
    fi
    cat > config/autoload/local.php <<PHP
<?php
return [
    'encryption' => [
        'key' => '${ENCRYPTION_KEY}',
    ],
    'mfa' => [
        'issuer' => '${MFA_ISSUER}',
    ],
    'app_url' => '${APP_URL}',
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'params' => [
                    'url' => 'pdo-mysql://${DB_USER}:${DB_PASSWORD}@${DB_HOST}/${DB_NAME}',
                ],
            ],
        ],
    ],
];
PHP
fi

if [[ ! -f config/autoload/auth.local.php ]]; then
    cat > config/autoload/auth.local.php <<PHP
<?php
return [
    'authentication' => [
        'pdo' => [
            'dsn'            => 'mysql:host=${DB_HOST}; dbname=${DB_NAME}',
            'table'          => 'user',
            'field'          => [
                'identity' => 'email',
                'password' => 'password',
            ],
            'sql_get_details' => 'SELECT id,status,mfa_enabled FROM user WHERE user.email = :identity',
            'username'       => '${DB_USER}',
            'password'       => '${DB_PASSWORD}',
        ],
    ],
];
PHP
fi

if [[ ! -f config/autoload/mail.local.php ]]; then
    MAIL_ENABLED="${MAIL_ENABLED:-false}"
    MAIL_SENDER="${MAIL_SENDER:-helpdesk@example.com}"
    MAIL_SMTP_NAME="${MAIL_SMTP_NAME:-localhost}"
    MAIL_SMTP_HOST="${MAIL_SMTP_HOST:-127.0.0.1}"
    MAIL_SMTP_PORT="${MAIL_SMTP_PORT:-25}"
    MAIL_SMTP_CONNECTION_CLASS="${MAIL_SMTP_CONNECTION_CLASS:-plain}"
    MAIL_SMTP_USERNAME="${MAIL_SMTP_USERNAME:-helpdesk@example.com}"
    MAIL_SMTP_PASSWORD="${MAIL_SMTP_PASSWORD:-change_me}"
    MAIL_SMTP_SSL="${MAIL_SMTP_SSL:-}"
    MAIL_SMTP_VERIFY_PEER="${MAIL_SMTP_VERIFY_PEER:-true}"
    MAIL_SMTP_VERIFY_PEER_NAME="${MAIL_SMTP_VERIFY_PEER_NAME:-true}"
    MAIL_SMTP_ALLOW_SELF_SIGNED="${MAIL_SMTP_ALLOW_SELF_SIGNED:-false}"

    MAIL_SSL_LINE=""
    if [[ -n "${MAIL_SMTP_SSL}" ]]; then
        MAIL_SSL_LINE="                'ssl'       => '${MAIL_SMTP_SSL}',"
    fi

    MAIL_VERIFY_PEER_LINE=""
    if [[ "${MAIL_SMTP_VERIFY_PEER}" == "false" ]]; then
        MAIL_VERIFY_PEER_LINE="                'verify_peer' => false,"
    fi

    MAIL_VERIFY_PEER_NAME_LINE=""
    if [[ "${MAIL_SMTP_VERIFY_PEER_NAME}" == "false" ]]; then
        MAIL_VERIFY_PEER_NAME_LINE="                'verify_peer_name' => false,"
    fi

    MAIL_ALLOW_SELF_SIGNED_LINE=""
    if [[ "${MAIL_SMTP_ALLOW_SELF_SIGNED}" == "true" ]]; then
        MAIL_ALLOW_SELF_SIGNED_LINE="                'allow_self_signed' => true,"
    fi

    cat > config/autoload/mail.local.php <<PHP
<?php
return [
    'mail' => [
        'enabled'      => ${MAIL_ENABLED},
        'sender'       => '${MAIL_SENDER}',
        'smtp_options' => [
            'name'              => '${MAIL_SMTP_NAME}',
            'host'              => '${MAIL_SMTP_HOST}',
            'port'              => ${MAIL_SMTP_PORT},
            'connection_class'  => '${MAIL_SMTP_CONNECTION_CLASS}',
            'connection_config' => [
                'username' => '${MAIL_SMTP_USERNAME}',
                'password' => '${MAIL_SMTP_PASSWORD}',
${MAIL_SSL_LINE}
${MAIL_VERIFY_PEER_LINE}
${MAIL_VERIFY_PEER_NAME_LINE}
${MAIL_ALLOW_SELF_SIGNED_LINE}
            ],
        ],
    ],
];
PHP
fi

if [[ "${AUTO_MIGRATE:-0}" == "1" ]]; then

    if [[ "${DEV_MODE:-false}" == "true" ]]; then
        composer development-enable || true
    fi

    echo "Waiting for database at ${DB_HOST}..."
    db_ready=0
    for attempt in {1..60}; do
        if mysqladmin ping -h "${DB_HOST}" -u "${DB_USER}" -p"${DB_PASSWORD}" \
            --protocol=TCP --connect-timeout=2 --ssl=0 --silent; then
            db_ready=1
            echo "Database is ready."
            break
        fi
        echo "Database not ready yet (attempt ${attempt}/60)."
        sleep 2
    done

    if [[ "${db_ready}" != "1" ]]; then
        echo "Database did not become ready in time."
        exit 1
    fi

    ./vendor/bin/doctrine-migrations status || true
    ./vendor/bin/doctrine-migrations migrate --no-interaction

    if [[ -n "${ADMIN_EMAIL:-}" && -n "${ADMIN_NAME:-}" ]]; then
        ADMIN_PASSWORD="${ADMIN_PASSWORD:-}"
        GENERATED_ADMIN_PASSWORD=0
        if [[ -z "${ADMIN_PASSWORD}" ]]; then
            ADMIN_PASSWORD="$(php -r 'echo bin2hex(random_bytes(12));')"
            GENERATED_ADMIN_PASSWORD=1
        fi

        USER_COUNT="$(php bin/console.php user:count | tail -n 1)"

        SKIP_ADMIN=0
        if [[ "${USER_COUNT}" != "0" ]]; then
            echo "Users already exist; skipping creation/update."
            SKIP_ADMIN=1
        fi

        if [[ "${SKIP_ADMIN}" != "1" ]]; then
            export ADMIN_PASSWORD
            php bin/console.php user:create \
                --email "${ADMIN_EMAIL}" \
                --name "${ADMIN_NAME}" \
                --password "${ADMIN_PASSWORD}" \
                --role "${ADMIN_ROLE:-Administrator}" \
                --status "${ADMIN_STATUS:-1}" \
                --force

            if [[ "${GENERATED_ADMIN_PASSWORD}" == "1" ]]; then
                echo "Generated ADMIN_PASSWORD for ${ADMIN_EMAIL}: ${ADMIN_PASSWORD}"
            fi
        fi
    fi
fi

if [[ -f docker/cron.d/casilium ]]; then
    CRON_SRC="/var/www/html/docker/cron.d/casilium"
    CRON_DEST="/etc/cron.d/casilium"
    cp "${CRON_SRC}" "${CRON_DEST}"
    chmod 0644 "${CRON_DEST}"
    crontab "${CRON_DEST}"
    cron
fi

exec "$@"
