---
name: laravel-hexagonal
description: Scaffold and generate hexagonal architecture components for Laravel bounded contexts
user-invocable: true
---

# Laravel Hexagonal Architecture Skill

You are helping maintain a Laravel project that follows **hexagonal architecture** (ports & adapters). When invoked, parse `$ARGUMENTS` to determine the action.

## Argument Parsing

- **No arguments** → Print an architecture overview (see "Architecture Guide" below)
- **`scaffold <Context>`** → Create the full directory structure and base files for bounded context `<Context>`
- **`generate use-case <Context> <Name>`** → Generate a use case class
- **`generate port <Context> <Name>`** → Generate a port (interface)
- **`generate adapter <Context> <Name>`** → Generate an infrastructure adapter

`<Context>` is a PascalCase bounded context name (e.g., `Booking`, `Payment`).

---

## Architecture Guide

When no arguments are given, explain this to the user:

### Folder Layout

```
app/
├── Domain/
│   └── <Context>/
│       ├── Entities/          # Rich domain models (not Eloquent)
│       ├── ValueObjects/      # Immutable value types
│       ├── Ports/             # Interfaces (driven & driving)
│       ├── Events/            # Domain events
│       └── Exceptions/        # Domain-specific exceptions
├── Application/
│   └── <Context>/
│       ├── UseCases/          # Application services / command handlers
│       ├── DTOs/              # Data transfer objects
│       └── Queries/           # Read-side query handlers
└── Infrastructure/
    └── <Context>/
        ├── Adapters/          # Implementations of ports
        │   ├── Persistence/   # Eloquent repositories
        │   └── External/      # Third-party API clients
        ├── Models/            # Eloquent models (data-mapping only)
        └── Providers/         # Service provider bindings
```

### Key Rules

1. **Domain layer** has zero Laravel dependencies. Pure PHP only.
2. **Ports** are interfaces defined in the Domain layer. They represent the contracts the domain needs.
3. **Adapters** live in Infrastructure and implement ports using Laravel/Eloquent/external services.
4. **Use cases** orchestrate domain logic. One public `execute()` method each.
5. **Controllers** are driving adapters — they live in the standard `app/Http/Controllers` directory and call use cases.
6. **Eloquent models** live in Infrastructure, not Domain. Domain entities are plain PHP classes.
7. **Service providers** in `Infrastructure/<Context>/Providers/` bind ports to adapters.

### Namespace Conventions (PSR-4)

| Namespace | Path |
|-----------|------|
| `Domain\<Context>\` | `app/Domain/<Context>/` |
| `Application\<Context>\` | `app/Application/<Context>/` |
| `Infrastructure\<Context>\` | `app/Infrastructure/<Context>/` |

---

## Action: `scaffold <Context>`

Create the following directory structure and files for the bounded context. Replace `<Context>` with the actual name.

### Directories to create

```
app/Domain/<Context>/Entities/
app/Domain/<Context>/ValueObjects/
app/Domain/<Context>/Ports/
app/Domain/<Context>/Events/
app/Domain/<Context>/Exceptions/
app/Application/<Context>/UseCases/
app/Application/<Context>/DTOs/
app/Application/<Context>/Queries/
app/Infrastructure/<Context>/Adapters/Persistence/
app/Infrastructure/<Context>/Adapters/External/
app/Infrastructure/<Context>/Models/
app/Infrastructure/<Context>/Providers/
```

### Files to create

**`app/Domain/<Context>/Ports/<Context>RepositoryInterface.php`**
```php
<?php

declare(strict_types=1);

namespace Domain\<Context>\Ports;

interface <Context>RepositoryInterface
{
    // Define repository contract methods here
}
```

**`app/Infrastructure/<Context>/Adapters/Persistence/Eloquent<Context>Repository.php`**
```php
<?php

declare(strict_types=1);

namespace Infrastructure\<Context>\Adapters\Persistence;

use Domain\<Context>\Ports\<Context>RepositoryInterface;

class Eloquent<Context>Repository implements <Context>RepositoryInterface
{
    // Implement repository methods using Eloquent
}
```

**`app/Infrastructure/<Context>/Providers/<Context>ServiceProvider.php`**
```php
<?php

declare(strict_types=1);

namespace Infrastructure\<Context>\Providers;

use Domain\<Context>\Ports\<Context>RepositoryInterface;
use Illuminate\Support\ServiceProvider;
use Infrastructure\<Context>\Adapters\Persistence\Eloquent<Context>Repository;

class <Context>ServiceProvider extends ServiceProvider
{
    public array $bindings = [
        <Context>RepositoryInterface::class => Eloquent<Context>Repository::class,
    ];
}
```

### Post-scaffold steps

1. **Update `composer.json`** — add these PSR-4 entries under `autoload.psr-4` if they don't already exist:
   ```json
   "Domain\\": "app/Domain/",
   "Application\\": "app/Application/",
   "Infrastructure\\": "app/Infrastructure/"
   ```
   Then run `composer dump-autoload`.

2. **Register the service provider** — add `Infrastructure\<Context>\Providers\<Context>ServiceProvider::class` to `bootstrap/providers.php`.

3. Place `.gitkeep` files in empty directories so they are tracked by git.

---

## Action: `generate use-case <Context> <Name>`

Create **`app/Application/<Context>/UseCases/<Name>.php`**:

```php
<?php

declare(strict_types=1);

namespace Application\<Context>\UseCases;

class <Name>
{
    public function __construct(
        // Inject ports (interfaces) here
    ) {}

    public function execute(/* DTO or primitive params */): mixed
    {
        // Implement use case logic
    }
}
```

---

## Action: `generate port <Context> <Name>`

Create **`app/Domain/<Context>/Ports/<Name>.php`**:

```php
<?php

declare(strict_types=1);

namespace Domain\<Context>\Ports;

interface <Name>
{
    // Define contract methods
}
```

---

## Action: `generate adapter <Context> <Name>`

Create **`app/Infrastructure/<Context>/Adapters/<Name>.php`**:

```php
<?php

declare(strict_types=1);

namespace Infrastructure\<Context>\Adapters;

class <Name>
{
    public function __construct(
        // Inject dependencies
    ) {}
}
```

After generating, remind the user to:
- Have the adapter implement the appropriate port interface
- Register the binding in the context's service provider
