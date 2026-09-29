# Changelog

## v2.3.0 - 2026-09-29

### Upgrading

Run migrations after upgrading, and clear the config and Doctrine proxy caches.

Due dates were previously stored in the business hours timezone rather than UTC.
They are UTC from this release. Existing rows are not rewritten, so if your
business hours are not UTC, due dates on tickets created before upgrading stay
in local time until those tickets close.

Dashboard figures will look very different. Resolution is now measured in
working hours rather than elapsed time, over a rolling 30 days, and reported as
a median.

### Added

- report time to first response

- record time on hold and report resolution net of it

- add canned responses


### Changed

- prepend changelog entries instead of regenerating

- exclude local config from the coding standard

- bind date parameters as dates

- drop the target delete helpers

- psalm ignore .phtml templates

- bump dompdf to 3.1.6 and php_codesniffer to 3.13.6


### Fixed

- clear the SLA target when a ticket is retyped

- label the agent card for what it measures

- say what the resolved card counts

- report resolution in working hours over a rolling window

- refuse to delete business hours an SLA still uses

- pause the SLA clock in working minutes while a ticket is on hold

- update SLA targets in place instead of replacing them

- store ticket timestamps as UTC

- pin pecl imap to 1.0.3 and refresh the channel before install


## v2.2.0 - 2026-05-29

### Added

- add SLA document PDF generation and sending


### Changed

- updated changelog


### Fixed

- order search results by newest first


## v2.1.2 - 2026-05-28

### Changed

- Updated changelog


### Fixed

- update dependencies to patch symfony/mime CVEs


## v2.1.1 - 2026-02-24

### Changed

- cleanup tests (deprecations)

- code cleanup for CountUser command

- Updated PHPUnit and dependencies


### Fixed

- allow contacts being created with same email

- TOTP Service not restoring error handler

- EntityManagerInterface:class and EntityManager:class difference instances


## v2.1.0 - 2026-02-11

### Added

- re-open tickets on reply, notify if closed

- add dedicated ticket search page with filters and pagination

- add ticket search autocomplete to navbar

- add percentage complete to titles on exec summary

- add executive report command and unresolved list section

- use dompdf for pdf generation

- add is_active support to organisation contacts

- improved dashboard layout

- improve agent email notifcations to use templates

- add tickets config with auto_close_days


### Changed

- add lsp server to phpactor

- refresh composer.lock after dependency update


### Fixed

- composer changelog to show full history

- hide ticket reply box when closed

- problem parsing junk from MS Outlook emails

- ticket rows per page now persists across pages

- block past due dates on create and update DBAL datetime exception handling

- force new contacts active and hide active toggle on create

- harden container startup and suppress apache servername warning

- order contacts by firstname, lastname

- prevent deletion of contacts with tickets and deactivate instead

- fix role updates to not insert if existing rows

- show msg on response email

- fix dashboard widgets

- sla compliance widget to not include service tickets

- remove create tickets from mail from gate mail check

- run cron inside app container

- avg resolution time calc and resolve only not resolve+close

- git-cliff composer command


## v2.0.0 - 2026-02-09

### Added

- clean up pasted email content in ticket textareas

- replace sonata/google-authenticator with in-house TOTP service

- upgrade Doctrine + mail stack to PHP 8.4 baseline


### Changed

- remove unused laminas deps and simplify password hashing


### Fixed

- readme


## v1.0.0 - 2026-02-06

### Added

- add docker bootstrap, docs, and admin setup workflow


### Changed

- fix phpcs gmp function imports

- update composer dependencies & fix tests

- cs-fix

- code cleanup

- Code cleanup (csfix)


### Fixed

- updates to use symfony-cache

- replace laminas-cache component with symfony-cache

- initialise null values for php 8

- fix bug trying to create persist a new org

- replace FILTER_SANITIZE_STRING with htmlspecialchars

- strict type errors

- deprecated PHP functions (FILTER_SANITIZE_STRING)

- wrong order of labels

- semantic error, rbac not loading from cache

- semantic error, rbac not loading from cache

- status_id incorrectly referenced queue_id

- fixed tests

- csfix

- csfix


