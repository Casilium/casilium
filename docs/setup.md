# Setup (Non-Docker)

## Requirements

- HTTP server (Apache or Nginx)
- PHP 8.2/8.3/8.4 with extensions:
  - apcu
  - curl
  - dom
  - iconv
  - json
  - pdo
  - pdo_mysql
  - session
  - simplexml
  - sodium
  - tokenizer
  - xml
  - xmlwriter
- MySQL 5.7/8.0

## Install

```bash
git clone https://github.com/Casilium/casilium.git
cd casilium
composer install
```

## Configuration

Copy the templates and update credentials:

```bash
cp config/autoload/local.php.dist config/autoload/local.php
cp config/autoload/auth.local.php.dist config/autoload/auth.local.php
```

Set an encryption key in `config/autoload/local.php`:

```php
'encryption' => [
    'key' => 'YOUR_HEX_KEY',
],
```

Generate a key:

```bash
php -r 'echo sodium_bin2hex(random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES)), PHP_EOL;'
```

Set an MFA issuer (shows in authenticator apps):

```php
'mfa' => [
    'issuer' => 'YOUR_ISSUER',
],
```

Optional application base URL for email templates:

```php
'app_url' => 'http://localhost:8080',
```

Set the Doctrine connection URL:

```php
'url' => 'mysql://USERNAME:PASSWORD@localhost/DATABASE_NAME',
```

Update `config/autoload/auth.local.php` with matching DB credentials:

```php
'authentication' => [
    'pdo' => [
        'dsn'   => 'mysql:host=localhost; dbname=YOUR_DBNAME',
        'table' => 'user',
        'field' => [
            'identity' => 'email',
            'password' => 'password',
        ],
        'sql_get_details' => 'SELECT id,status,mfa_enabled FROM user WHERE user.email = :identity',
        'username'        => 'YOUR_USERNAME',
        'password'        => 'YOUR_PASSWORD',
    ],
],
```

## Database Migrations

```bash
./vendor/bin/doctrine-migrations status
./vendor/bin/doctrine-migrations migrate
```

Create an admin user:

```bash
php bin/console.php user:create --email "admin@example.com" --name "Administrator" --password "ChangeMe123" --role "Administrator"
```

Ensure scripts are executable:

```bash
chmod a+rx ./vendor/bin/*
```

## Web Server Configuration

Serve the `public` directory as the document root.

Example Apache virtual host:

```apacheconf
<VirtualHost *:80>
    ServerName example.com
    DocumentRoot /path/to/project/public
</VirtualHost>
```

## php.ini

Enable APCu for CLI:

```ini
apc.enable_cli = 1
```

## Disable Development Mode (Production)

```bash
composer development-disable
```
