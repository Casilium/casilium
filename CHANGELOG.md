# Changelog

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


