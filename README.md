[![Laravel Lint and Test](https://github.com/koshuang/laravel-hexagonal-architecture/actions/workflows/laravel_lint_and_test.yml/badge.svg)](https://github.com/koshuang/laravel-hexagonal-architecture/actions/workflows/laravel_lint_and_test.yml)
[![StyleCI](https://github.styleci.io/repos/532449966/shield?style=plastic)](https://github.styleci.io/repos/532449966)
[![SonarCloud](https://sonarcloud.io/api/project_badges/measure?project=koshuang_laravel-hexagonal-architecture&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=koshuang_laravel-hexagonal-architecture)

# laravel-hexagonal-architecture

This is an implementation of Hexagonal Architecture for Laravel with quality enforcement via **PHPStan**, **Deptrac**, **PHP Insights**, and **PHP CS Fixer**.

The example is based on https://github.com/thombergs/buckpal which author of the book [Get Your Hands Dirty on Clean Architecture: A hands-on guide to creating clean web applications with code examples in Java](https://pubhtml5.com/dtiq/edqp).

YouTube Talk: https://www.youtube.com/watch?v=cPH5AiqLQTo&t=1684s

## Folder structure

```
Modules
└── Account
    ├── Application
    ├── Domain
    │   ├── Entities
    │   └── ValueObjects
    ├── Infrastructure
    │   ├── Adapter
    │   │   ├── In
    │   │   │   ├── Console
    │   │   │   └── Web
    │   │   │       ├── Http
    │   │   │       │   ├── Controllers
    │   │   │       │   ├── Middleware
    │   │   │       │   └── Requests
    │   │   │       ├── Resources
    │   │   │       │   ├── assets
    │   │   │       │   ├── lang
    │   │   │       │   └── views
    │   │   │       └── Routes
    │   │   └── Out
    │   │       └── Persistence
    │   │           ├── Database
    │   │           │   ├── Factories
    │   │           │   ├── Migrations
    │   │           │   └── Seeders
    │   │           └── ElequentModels (Original Entities folder from Laravel)
    │   ├── Config
    │   └── Providers
    └── Tests
        ├── Common
        ├── Feature
        └── Unit
            └── Domain
                └── Entities
```

## What have I done?

- Architecture enforcement with **Deptrac**: enforces layer dependency rules (Infrastructure → Application → Domain) and ensures Domain never depends on Framework details like Facades
- Static analysis with **PHPStan** (Level 8)
- Code style with **PHP CS Fixer**
- Code quality with **PHP Insights** (100/100)
- Use [laravel-modules](https://github.com/nWidart/laravel-modules) package to create a `Account` module. The Account module should reflect to BoundedContext for DDD.
- Map Laravel boilerplate into Infrastructure
- Use TDD to gradually port code from https://github.com/thombergs/buckpal
    - Add test for Domain Layer of Account
        - calculate balance
        - withdraw

## Contributing

Feel free to contribute :)

## License

The example is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
