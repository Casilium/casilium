# Docker

Casilium can be run locally using Docker for development or testing.

## Quick Start

```bash
docker compose up --build
```

The app will be available on port `8080` by default.

## Configuration

The Docker entrypoint creates `config/autoload/local.php` and
`config/autoload/auth.local.php` automatically if they do not exist, using
values from `docker-compose.yml`.

Set `MAIL_ENABLED=false` in `docker-compose.yml` (or the environment) to disable
all outbound/inbound mail handling. When disabled, the application will skip
sending ticket notifications, cron-driven digests, and the mailbox import
command so you can run containers without working SMTP/IMAP credentials.

If `ADMIN_EMAIL` and `ADMIN_NAME` are set, the entrypoint creates an admin
account only when the user table is empty. If `ADMIN_PASSWORD` is not provided,
it is generated and printed once to container logs on first run.

## Migrations

Migrations are run automatically by default. To disable auto-migration, set
`AUTO_MIGRATE=0` for the `app` service and run them manually:

```bash
docker compose exec app ./vendor/bin/doctrine-migrations status
docker compose exec app ./vendor/bin/doctrine-migrations migrate
```

## Development Mode

Development mode is enabled by default. To disable it, set `DEV_MODE=false`
for the `app` service.

## Stop or Reset

Stop containers:

```bash
docker compose down
```

Reset the database volume (fresh install):

```bash
docker compose down -v
```
