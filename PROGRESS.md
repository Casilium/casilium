# Dependency Cleanup Progress

## Branch: chore/deps-cleanup

### Packages Removed (Direct)

- `mezzio/mezzio-tooling` - CLI scaffolding tool, not used
- `doctrine/cache` - abandoned, no longer needed with ORM 3.x
- `laminas/laminas-crypt` - unused
- `laminas/laminas-log` - unused
- `laminas/laminas-math` - unused
- `laminas/laminas-zendframework-bridge` - legacy compatibility layer, no longer needed
- `laminas/laminas-mail` - abandoned, replaced with `symfony/mailer` + `ddeboer/imap`

### Packages Removed (Transitive)

These were removed automatically when their parent dependencies were removed/upgraded:

- `doctrine/annotations` - replaced by PHP 8 attributes in ORM 3.x
- `laminas/laminas-cli` - only needed by mezzio-tooling
- `laminas/laminas-code` - only needed by mezzio-tooling
- `symfony/process` - only needed by mezzio-tooling
- `symfony/polyfill-php72` - no longer needed on PHP 8.x
- `laminas/laminas-loader` - only needed by laminas-mail
- `laminas/laminas-mime` - only needed by laminas-mail

### New Packages Added

- `symfony/mailer` - for sending emails (replaces laminas-mail SMTP)
- `ddeboer/imap` - for reading IMAP mailboxes (replaces laminas-mail IMAP)
- `symfony/mime` - MIME handling (transitive dep of symfony/mailer)

### Major Upgrades

| Package | From | To |
|---------|------|-----|
| `doctrine/orm` | 2.20.9 | 3.6.2 |
| `doctrine/dbal` | 3.10.4 | 4.4.1 |
| `doctrine/persistence` | 3.4.3 | 4.1.1 |
| `roave/psr-container-doctrine` | 4.2.0 | 6.1.0 |
| `ramsey/uuid-doctrine` | 1.8.2 | 2.1.0 |
| `doctrine/migrations` | 3.6.0 | 3.9.x |
| `doctrine/doctrine-laminas-hydrator` | 3.6.1 | 3.7.0 |
| `mezzio/mezzio` | 3.23.2 | 3.27.0 |
| `mezzio/mezzio-router` | 3.19.0 | 4.2.0 |
| `laminas/laminas-stratigility` | 3.14.0 | 4.3.0 |
| `phpunit/phpunit` | 9.x | 10.x |

### Code Changes for Doctrine ORM 3.x / DBAL 4.x Compatibility

- `src/Ticket/src/Repository/TicketRepository.php` - Changed `$this->_em` to `$this->getEntityManager()`
- `src/User/src/Repository/UserRepository.php` - Changed `$this->_em` to `$this->getEntityManager()`
- `src/App/src/Doctrine/UtcDateTimeType.php` - Updated method signatures for DBAL 4.x
- `test/TicketTest/Repository/TicketRepositoryTest.php` - Removed reflection hack for `_em` property
- `test/TicketTest/Service/TicketServiceTest.php` - Removed `->willReturn(null)` on void `flush()` method

### Code Changes for Mail Replacement

- `src/MailService/src/Service/MailService.php` - Rewritten to use `symfony/mailer`
- `src/MailService/src/Service/MailService.php` - Added TLS override support + caught transport exceptions, accepts PSR logger
- `src/Ticket/src/Service/MailReader.php` - Rewritten to use `ddeboer/imap` with explicit SEEN flag handling
- `src/Ticket/src/Parser/EmailMessageParser.php` - Simplified, removed Laminas dependencies
- `docker/entrypoint.sh` - Added env knobs for SMTP verify flags when auto-generating mail config
- `config/autoload/mail.local.php*` - Documented verify/self-signed options; switched defaults to hostname to avoid TLS mismatch
- `src/Ticket/src/Handler/EditQueueHandler.php` - Redirect back to queue list after successful save
- `src/Ticket/src/Command/CreateTicketsFromEmail.php` - Fixed default-priority handling to avoid crashes when creating new tickets

### Type-safety Fixes for Doctrine Entities

- `src/Ticket/src/Entity/Queue.php` - Normalized `exchangeArray()` to cast form data before assigning to typed props (prevents PHP 8.4 type errors)

### composer.json Changes

- PHP constraint updated to `~8.4.0 || ~8.5.0` (minimum PHP 8.4 required)
- Added `ext-imap` requirement
- Removed `mezzio` script (was for mezzio-tooling)

### Infrastructure Changes

- `Dockerfile` - Updated from PHP 8.2 to 8.4, added `ext-imap` extension
- `.github/workflows/ci.yml` - Updated from PHP 8.3 to 8.4
- `README.md` - Updated PHP badge to 8.4+

### Package Count

- Before: 114 packages
- After: 109 packages
- Net removed: 5 packages

### Still Abandoned (Cannot Easily Remove)

| Package | Reason | Potential Fix |
|---------|--------|---------------|
| `laminas/laminas-json` | Required by laminas-view | Stuck with it |
| `sonata-project/google-authenticator` | Direct dependency for MFA | Find replacement |

### Notes

- Minimum PHP version is now 8.4 (roave/psr-container-doctrine 6.x requires it)
- PHPUnit suite now at **583 tests / 1345 assertions** on PHP 8.5, pending deprecation fix for `ReflectionMethod::setAccessible()` in importer tests
- Composer PHAR currently emits `$http_response_header` deprecation noise; harmless but noisy during CI

### Test Coverage

- Added `test/MailServiceTest/Service/MailServiceTest.php` to verify DSN generation + transport error handling around Symfony Mailer
- Added `test/TicketTest/Service/MailReaderTest.php` to cover HTML/text body parsing for the IMAP reader
- Added `test/TicketTest/Command/CreateTicketsFromEmailTest.php` to regress SLAs when creating tickets (needs reflection cleanup as noted above)
- Ran `composer test` and `composer cs-check`; both pass locally with the composer deprecation warning noted above

### Follow-up / Risks

- Remaining abandoned dep: `sonata-project/google-authenticator`. Plan is to replace it with an in-repo TOTP helper so MFA no longer depends on an unmaintained package.
- Need to refactor importer tests to avoid `ReflectionMethod::setAccessible()` (deprecated as of PHP 8.5) before CI upgrades beyond 8.5.
- Monitor queue edit UX for regressions; entity casting fixes resolved type errors, but we should ensure Laminas form validation stays untouched in future edits.

### Verification Needed

- [ ] Docker build works
- [x] Send test email (manual)
- [x] Receive email, verify ticket created (manual)
- [x] Reply to ticket email, verify response added (manual)
