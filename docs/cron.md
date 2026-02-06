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
