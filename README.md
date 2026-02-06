# Casilium

![CI](https://github.com/Casilium/casilium/actions/workflows/ci.yml/badge.svg?branch=master)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777bb4)
![License](https://img.shields.io/github/license/Casilium/casilium)
[![Buy Me a Coffee](https://img.shields.io/badge/Buy%20Me%20a%20Coffee-support-ffdd00?logo=buymeacoffee)](https://buymeacoffee.com/sheridans)


Casilium is a self-hosted helpdesk and ticketing platform built on Mezzio and
Doctrine ORM. It is designed for teams that want a lightweight, configurable
service desk without SaaS lock-in.

## Features

- Multi-organisation workflows with sites and contacts.
- SLA-driven due dates based on business hours.
- RBAC permissions and optional MFA.
- Email-based ticket intake.
- Scheduled notifications and overdue digests.
- Modular architecture (entities, services, handlers, commands per module).

## Why Casilium

Casilium gives you a clear ticketing workflow with SLA-aware due dates, robust
role-based permissions, and optional MFA, without the overhead of larger service
desk suites. It is easy to self-host and keep under your control.

## Who It Is For

Originally built for IT support teams, Casilium also works well for any group
handling internal or customer-facing requests where SLAs, queues, and auditability
matter (e.g. facilities, HR, operations, or managed service teams).

## Documentation

- Setup (non-Docker): `docs/setup.md`
- Docker: `docs/docker.md`
- Usage: `docs/usage.md`
- Cron jobs: `docs/cron.md`

## Quick Start (Docker)

```bash
docker compose up --build
```

See `docs/docker.md` for full Docker instructions and `docs/setup.md` for manual setup.

License
-------
Casilium is released under the Apache 2.0 license. See the included LICENSE.txt
file for details of the General Public License.

Casilium is uses several open source projects, including
[Laminas](https://getlaminas.org/),
[Mezzio](https://docs.mezzio.dev/),
[Doctrine](https://www.doctrine-project.org/),
[Bootstrap](https://getbootstrap.com/),
[Font-Awesome](https://fontawesome.com/),
[jQuery](https://jquery.com/).
