# laravel-hexagonal-architecture

This is an implementation of Hexagonal Architecture for Laravel 9. The example is based on https://github.com/thombergs/buckpal.

The repo is the author of `Get Your Hands Dirty on Clean Architecture: A hands-on guide to creating clean web applications with code examples in Java`.

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

- Run StyleCI, Larastan on CircleCI
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
