# Cron Jobs

This project relies on scheduled console commands for background tasks. Configure them via your system crontab.

## Example Schedule

```cron
# Update due dates for waiting/on-hold tickets.
*/3  * * * *  /var/www/casilium/bin/console.php ticket:update-waiting > /dev/null
# Create tickets from the mailbox.
*/5  * * * *  /var/www/casilium/bin/console.php ticket:create-from-mail > /dev/null
# Close tickets marked as resolved.
0    2 * * *  /var/www/casilium/bin/console.php ticket:close-resolved > /dev/null
# Notify queues about tickets due soon (2 hours).
8 6-20 * * *  /var/www/casilium/bin/console.php ticket:notifications 2 hours > /dev/null
# Notify queues about tickets due soon (1 hour).
*/10 6-20 * * * /var/www/casilium/bin/console.php ticket:notifications 1 hours > /dev/null
# Daily overdue ticket digest (per queue).
0    7 * * *  /var/www/casilium/bin/console.php ticket:overdue-digest > /dev/null
```

Adjust timing, cadence, and paths for your environment.

The auto-close window for `ticket:close-resolved` is configurable via
`config/autoload/tickets.global.php` (`tickets.auto_close_days`, default 2).

> **Note:** Commands that send or ingest email (`ticket:create-from-mail`,
> `ticket:notifications`, `ticket:overdue-digest`) automatically skip execution
> when the `mail.enabled` flag (or `MAIL_ENABLED` env var in Docker) is set to
> `false`, which is useful for development environments without SMTP/IMAP.

When running via Docker, the same schedule resides in `docker/cron.d/casilium`
and is executed by the cron daemon that starts inside the `app` container. All
job output goes to `docker compose logs -f app`.
