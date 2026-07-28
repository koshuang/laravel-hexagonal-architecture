[![Laravel Lint and Test](https://github.com/koshuang/laravel-hexagonal-architecture/actions/workflows/laravel_lint_and_test.yml/badge.svg)](https://github.com/koshuang/laravel-hexagonal-architecture/actions/workflows/laravel_lint_and_test.yml)
[![SonarCloud](https://sonarcloud.io/api/project_badges/measure?project=koshuang_laravel-hexagonal-architecture&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=koshuang_laravel-hexagonal-architecture)

# Laravel Hexagonal Architecture

A **Laravel 13** implementation of **Hexagonal Architecture** (Ports & Adapters) with full **CI/CD quality enforcement**.

Built with **PHP 8.4+** (Docker image: PHP 8.5), static analysis at **PHPStan Level 9**, **Deptrac** layer dependency checks, and **100/100 PHP Insights** score.

The example domain is a money transfer use case ported from [thombergs/buckpal](https://github.com/thombergs/buckpal) — the companion code for the book [*Get Your Hands Dirty on Clean Architecture*](https://pubhtml5.com/dtiq/edqp).

YouTube Talk: https://www.youtube.com/watch?v=cPH5AiqLQTo&t=1684s

---

## Stack

| Layer | Tool |
|-------|------|
| **Framework** | Laravel 13 |
| **PHP** | ^8.4 (Docker: 8.5) |
| **Modules** | nwidart/laravel-modules ^13 |
| **Testing** | Pest ^4 (PHPUnit 12) |
| **Static Analysis** | PHPStan Level 9 + Larastan |
| **Architecture** | Deptrac (layer rules) |
| **Code Style** | PHP CS Fixer + PHP CodeSniffer |
| **Code Quality** | PHP Insights (100/100) |
| **Rector** | Automated PHP upgrade refactoring |

---

## Folder Structure

```
Modules
└── Account
    ├── Application          ← Use cases, ports, services
    │   ├── Port
    │   │   ├── In           ← Inbound ports (interfaces)
    │   │   └── Out          ← Outbound ports (interfaces)
    │   └── Services         ← Business logic / use case implementations
    ├── Domain               ← Enterprise business rules
    │   ├── Entities
    │   └── ValueObjects
    ├── Infrastructure       ← Framework adapters, persistence, web
    │   ├── Adapter
    │   │   ├── In
    │   │   │   ├── Console
    │   │   │   └── Web
    │   │   │       ├── Http
    │   │   │       │   ├── Controllers
    │   │   │       │   ├── Middleware
    │   │   │       │   └── Requests
    │   │   │       ├── Resources
    │   │   │       ├── Routes
    │   │   │       └── Views
    │   │   └── Out
    │   │       └── Persistence
    │   ├── Config
    │   └── Providers
    └── Tests
        ├── Common           ← Shared test data / factories
        ├── Feature
        └── Unit
            └── Domain
                └── Entities
```

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
```

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
| 4. **PHP CS Fixer** | `composer phpcs` | PSR-12 coding style |
| 5. **Tests** | `composer test` | Pest test suite (19 tests, 44 assertions) |

### Individual Commands (via Docker)

```bash
# Static analysis
docker-compose run --rm app composer phpstan

# Architecture enforcement
docker-compose run --rm app composer deptrac

# Code quality insights
docker-compose run --rm app vendor/bin/phpinsights --no-interaction

# Auto-fix code style
docker-compose run --rm app composer phpcs

# Run tests
docker-compose run --rm app composer test

# Auto-refactor (Rector + Deptrac + Insights + PHP CS Fixer)
docker-compose run --rm app composer refactor

# Dry-run refactor (preview only)
docker-compose run --rm app composer refactor:dry-run
```

---

## What's Implemented

- ✅ **Hexagonal Architecture**: Ports & Adapters pattern with strict layer separation
- ✅ **Deptrac Architecture Enforcement**: Automated dependency rules in CI
- ✅ **PHPStan Level 9**: Maximum static analysis rigor
- ✅ **PHP Insights 100/100**: Code, Complexity, Architecture, and Style all at perfect score
- ✅ **PHP CS Fixer**: PSR-12 coding standards enforced
- ✅ **Rector**: Automated PHP version upgrade refactoring
- ✅ **Laravel Modules** (`nwidart/laravel-modules`): Account module as a DDD Bounded Context
- ✅ **TDD**: Test-driven development with Pest
- ✅ **CI Pipeline**: GitHub Actions runs all quality checks on push/PR
- ✅ **Docker**: Consistent dev environment (PHP 8.5)

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
5. **Pest Tests** — Full test suite

---

## Contributing

Feel free to contribute :)

---

## License

The example is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
