# Changelog

## v2.0.0 - 2026-02-09

### Added

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


