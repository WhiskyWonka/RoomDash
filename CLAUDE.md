# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Environment

The project runs entirely in Docker via Laravel Sail. Node.js/npm commands must be run inside the frontend container.

```bash
# Start all services
./vendor/bin/sail up -d

# Initial setup (install deps, copy .env, generate key, migrate)
composer setup

# Run dev server with queue + logs
composer dev

# Run tests
composer test                    # or: ./vendor/bin/pest
./vendor/bin/pest tests/Unit     # single suite
./vendor/bin/pest --filter=Name  # single test

# Lint PHP
./vendor/bin/pint

# Frontend (run inside container)
docker exec roomdash-frontend-1 npm run build
docker exec roomdash-frontend-1 npm run dev

# Migrations
./vendor/bin/sail artisan migrate              # central DB
./vendor/bin/sail artisan tenants:migrate      # tenant schemas

# Rebuild frontend container (after adding npm deps)
docker compose build --no-cache frontend
docker compose up -d --no-deps --force-recreate -V frontend
```

**Services:** Laravel on port 80 (`APP_PORT`), React on port 5173 (`FRONTEND_PORT`), PostgreSQL on port 5432.

## Architecture

### Hexagonal / Ports & Adapters

The backend follows hexagonal architecture with PSR-4 namespaces mapped directly:

- **`Domain\`** (`app/Domain/`) — Framework-agnostic business logic. Entities are readonly immutable classes using `DateTimeImmutable` (not Carbon). Ports define interfaces for dependency inversion.
- **`Application\`** (`app/Application/`) — Use cases layer (not yet implemented; controllers call repositories directly for now).
- **`Infrastructure\`** (`app/Infrastructure/`) — Framework-specific adapters implementing domain ports. Eloquent models live here, never exposed outside — repositories convert to domain entities via `toEntity()`.
- **`App\`** (`app/Http/`, etc.) — Standard Laravel HTTP layer.

Bindings are registered in `TenancyServiceProvider`: `TenantRepositoryInterface` → `EloquentTenantRepository`, `SchemaManagerInterface` → `PostgresSchemaManager`.

### Multi-Tenancy (stancl/tenancy)

- **Strategy:** PostgreSQL schema-per-tenant (not separate databases). Schemas named `tenant{uuid}`.
- **Identification:** Subdomain-based (`InitializeTenancyBySubdomain` middleware).
- **Lifecycle:** On `TenantCreated`, a synchronous job pipeline runs `CreateDatabase` → `MigrateDatabase`. On `TenantDeleted`, `DeleteDatabase` runs.
- **Migrations:** Central in `database/migrations/`, tenant-specific in `database/migrations/tenant/`.
- **Central domains:** Configured via `TENANCY_CENTRAL_DOMAINS` env var.
- **Routes:** Central API in `routes/api.php`, tenant-scoped in `routes/tenant.php` (loaded by `TenancyServiceProvider`).

### Frontend

Standalone React 19 + TypeScript + Vite SPA in `frontend/`. Uses Tailwind CSS v4 with `@tailwindcss/vite` plugin and shadcn/ui-style components (Radix UI primitives).

- Path alias: `@/*` → `./src/*`
- API client: `src/lib/api.ts` — typed fetch wrapper proxied via Vite to Laravel
- Vite proxy strips port from Host header to support subdomain-based tenant identification

### Docker

- `laravel.test` — PHP 8.5 / Sail (backend)
- `frontend` — Node 22 Alpine (React dev server). Uses anonymous volume for `node_modules` — must rebuild container image when adding npm dependencies.
- `pgsql` — PostgreSQL 18. A `testing` database is auto-created for tests.
