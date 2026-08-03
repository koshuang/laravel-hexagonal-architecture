[![Laravel Lint and Test](https://github.com/koshuang/laravel-hexagonal-architecture/actions/workflows/laravel_lint_and_test.yml/badge.svg)](https://github.com/koshuang/laravel-hexagonal-architecture/actions/workflows/laravel_lint_and_test.yml)
[![SonarCloud](https://sonarcloud.io/api/project_badges/measure?project=koshuang_laravel-hexagonal-architecture&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=koshuang_laravel-hexagonal-architecture)

# Laravel Hexagonal Architecture

A **Laravel 13** implementation of **Hexagonal Architecture** (Ports & Adapters) with full **CI/CD quality enforcement** and a **TypeScript SPA frontend** that mirrors the same clean architecture on the client side.

Built with **PHP 8.4+** (Docker image: PHP 8.4), static analysis at **PHPStan Level 9**, **Deptrac** layer dependency checks, **100/100 PHP Insights** score, and **111 Vitest** frontend tests.

The example domain is a money transfer use case ported from [thombergs/buckpal](https://github.com/thombergs/buckpal) — the companion code for the book [*Get Your Hands Dirty on Clean Architecture*](https://pubhtml5.com/dtiq/edqp).

YouTube Talk: https://www.youtube.com/watch?v=cPH5AiqLQTo&t=1684s

---

## Stack

| Layer | Tool |
|-------|------|
| **Framework** | Laravel 13 |
| **PHP** | ^8.4 (Docker: 8.4) |
| **Modules** | nwidart/laravel-modules ^13 |
| **Backend Testing** | Pest ^4 (PHPUnit 12) — 47 tests, 179 assertions |
| **Static Analysis** | PHPStan Level 9 + Larastan |
| **Architecture** | Deptrac (layer rules) |
| **Code Style** | PHP CS Fixer + PHP CodeSniffer |
| **Code Quality** | PHP Insights (100/100) |
| **Rector** | Automated PHP upgrade refactoring |
| **Frontend** | TypeScript + Vite + Axios |
| **Frontend Testing** | Vitest — 7 test files, **111 tests** |

---

## Folder Structure

```
laravel-hexagonal-architecture/
└── Modules
    └── Account
        ├── Application                ← Backend use cases, ports, services
        │   ├── Port
        │   │   ├── In                 ← Inbound ports (interfaces)
        │   │   └── Out                ← Outbound ports (interfaces)
        │   └── Services               ← Business logic / use case implementations
        ├── Domain                     ← Enterprise business rules
        │   ├── Entities
        │   └── ValueObjects
        ├── Infrastructure             ← Framework adapters, persistence, web
        │   ├── Adapter
        │   │   ├── In
        │   │   │   ├── Console
        │   │   │   └── Web
        │   │   │       ├── Http
        │   │   │       │   ├── Controllers
        │   │   │       │   ├── Middleware
        │   │   │       │   └── Requests
        │   │   │       ├── Resources
        │   │   │       │   └── assets/ts/   ← Frontend SPA (see below)
        │   │   │       ├── Routes
        │   │   │       └── Views
        │   │   └── Out
        │   │       └── Persistence
        │   ├── Config
        │   └── Providers
        └── Tests
            ├── Common
            ├── Feature
            └── Unit
```

### Frontend SPA (`/app`)

The SPA lives inside the Account module at `Modules/Account/Infrastructure/Adapter/In/Web/Resources/assets/ts/` and follows the same Hexagonal Architecture pattern:

```
assets/ts/
├── domain/                           ← Domain layer (entities)
│   ├── Money.ts                      ← Value Object: amount operations
│   ├── Account.ts                    ← Entity: id, balance, createdAt
│   └── Activity.ts                   ← Entity: type, amounts, participants
├── application/
│   ├── ports/
│   │   └── AccountRepository.ts      ← Outbound port (interface)
│   └── use-cases/
│       ├── GetAccounts.ts            ← List all accounts
│       ├── GetAccountDetail.ts       ← Account detail + activities
│       └── SendMoney.ts              ← Send money with validation
├── infrastructure/
│   ├── api/
│   │   └── ApiAccountRepository.ts   ← HTTP adapter (Laravel API)
│   └── ui/
│       └── Dashboard.ts              ← SPA UI (state, rendering, events)
├── main.ts                           ← Entry point
└── vitest.config.ts                  ← Vitest test configuration
```

The frontend mirrors the backend's dependency rule: **Infrastructure → Application → Domain** (never inward).

### Layer Dependency Rules (enforced by Deptrac)

```
Infrastructure → Application → Domain
```

- **Domain** — Never depends on framework details (no Facades, no `Illuminate` classes)
- **Application** — Depends only on Domain interfaces
- **Infrastructure** — Implements Application ports, bridges to Laravel framework

---

## Development

### Quick Start (Docker)

```bash
# Build & start containers
docker-compose up -d

# Setup environment file & app key
cp .env.example .env
docker-compose run --rm app php artisan key:generate

# Install dependencies
docker-compose run --rm app composer install

# Install frontend dependencies & build
npm install
npx vite build
```

### Local Package Development

This demo consumes the published `koshuang/laravel-hexagonal` package from
Packagist using the stable `^0.2` constraint.

When developing a new package version locally, publish a new package tag before
updating this demo. A temporary VCS repository can also be used during package
development, but it is intentionally not part of the demo's normal setup.

```bash
composer update koshuang/laravel-hexagonal --with-dependencies
php artisan hexagonal:install
php artisan hexagonal:make-module Example
php artisan hexagonal:validate
```

The existing `Account` module remains the reference implementation. The generated
`Example` module is only a local integration check and should not be committed.

### Hexagonal package

This repository is the companion Demo for
[`koshuang/laravel-hexagonal`](https://github.com/koshuang/laravel-hexagonal),
which is published on [Packagist](https://packagist.org/packages/koshuang/laravel-hexagonal)
and installed with the `^0.1` constraint in `composer.json`.

The package provides the reusable architecture setup:

- `hexagonal:install` adds the Shared contracts, Deptrac configuration, Composer autoloading, and required package settings.
- `hexagonal:make-module` creates a generic Domain, Application, and Infrastructure module scaffold.
- `hexagonal:validate` runs the architecture dependency check used by CI.
- `vendor:publish --tag=hexagonal-stubs` publishes package defaults for customization.

The Demo still keeps `stubs/hexagonal-architecture` because those files customize
`nwidart/laravel-modules` for this application. They include Demo-specific module
defaults such as frontend assets, views, and Inertia support; they are not a copy
of the package's generic `hexagonal:make-module` stubs. New projects should use
the package defaults first and publish/customize them only when their application
needs a different scaffold.

### Access the SPA

Once the server is running, visit **http://localhost:8000/app**.

- Click **"Seed Demo Data"** to create 2 accounts with sample transaction history
- Click on any account card to view its transactions
- Use the **"Send Money"** form to transfer funds between accounts
- The first account seeded has $0 balance (self-transactions); the second receives deposits from the first

### One-command quality check

```bash
docker-compose run --rm app composer lint
```

Runs all quality tools in sequence:

| Step | Command | What it checks |
|------|---------|----------------|
| 1. **PHPStan** | `composer phpstan` | Static analysis at Level 9 |
| 2. **Deptrac** | `composer deptrac` | Layer dependency rules |
| 3. **PHP Insights** | `composer insights` | Code quality scoring (target: 100/100) |
| 4. **PHP coding standards** | `composer phpcs:check` | PHP-CS-Fixer and PHPCS checks |
| 5. **Tests** | `composer test` | Pest test suite (47 tests, 179 assertions) |

### Frontend Tests

```bash
# Run frontend tests (7 files, 111 tests)
npx vitest run --config Modules/Account/Infrastructure/Adapter/In/Web/Resources/assets/ts/vitest.config.ts
```

### Individual Commands (via Docker)

```bash
# Static analysis
docker-compose run --rm app composer phpstan

# Architecture enforcement
docker-compose run --rm app composer deptrac

# Code quality insights
docker-compose run --rm app vendor/bin/phpinsights --no-interaction

# Check code style without modifying files
docker-compose run --rm app composer phpcs:check

# Auto-fix code style
docker-compose run --rm app composer phpcs

# Run backend tests
docker-compose run --rm app composer test

# Auto-refactor (Rector + Deptrac + Insights + PHP CS Fixer)
docker-compose run --rm app composer refactor

# Dry-run refactor (preview only)
docker-compose run --rm app composer refactor:dry-run
```

---

## What's Implemented

- ✅ **Hexagonal Architecture**: Ports & Adapters pattern with strict layer separation (backend & frontend)
- ✅ **Frontend SPA**: TypeScript clean architecture SPA at `/app` with state management and notifications
- ✅ **Deptrac Architecture Enforcement**: Automated dependency rules in CI
- ✅ **PHPStan Level 9**: Maximum static analysis rigor
- ✅ **PHP Insights 100/100**: Code, Complexity, Architecture, and Style all at perfect score
- ✅ **PHP CS Fixer**: PSR-12 coding standards enforced
- ✅ **Rector**: Automated PHP version upgrade refactoring
- ✅ **Laravel Modules** (`nwidart/laravel-modules`): Account module as a DDD Bounded Context
- ✅ **TDD**: Test-driven development with Pest (47 tests) + Vitest (111 tests)
- ✅ **CI Pipeline**: GitHub Actions runs all quality checks on push/PR
- ✅ **Docker**: Consistent dev environment (PHP 8.5)
- ✅ **Modern PHP**: Asymmetric visibility, Pipe Operator (`|>`), Property Hooks, readonly classes

### Architecture Tests

Dependency direction is enforced via both **Deptrac** (`deptrac.yaml`) and **Pest** (`tests/ArchitectureTest.php`):

- Infrastructure → Application → Domain (no reverse dependencies)
- Domain must not depend on `Illuminate` framework classes (except `Support`)
- Application must not depend on `Illuminate` Facades

### Domain Features

- Account balance calculation (deposit/withdrawal windows)
- Money transfer use case with threshold validation
- Activity window tracking

---

## CI/CD Pipeline

Every push to `main` and pull request triggers:

1. **PHPStan** — Level 9 static analysis
2. **PHP CS Fixer** — Coding style enforcement
3. **PHP Insights** — Quality score ≥ 80 (Style ≥ 95)
4. **Deptrac** — Architecture layer rules
5. **Pest Tests** — Full test suite (47 tests)

---

## Contributing

Feel free to contribute :)

---

## License

The example is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
