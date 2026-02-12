# Architecture Patterns Discovered

## Domain Layer (`app/Domain/`)
- Entities are `final class` with `readonly` promoted constructor properties
- Use `DateTimeImmutable` (never Carbon)
- Implement `JsonSerializable` with `jsonSerialize(): array`
- Dates formatted with `->format('c')` (ISO 8601)
- Ports are interfaces in `Domain/{Context}/Ports/`
- Entities in `Domain/{Context}/Entities/`

## Infrastructure Layer (`app/Infrastructure/`)
- Eloquent models in `Infrastructure/{Context}/Models/`
- Repository adapters in `Infrastructure/{Context}/Adapters/`
- Service providers in `Infrastructure/{Context}/Providers/`
- Models use `HasUuids` trait, UUID primary keys
- Models have `toEntity()` method to convert to domain entities
- Sensitive fields (2FA secrets, recovery codes) encrypted with `Crypt` facade via accessors/mutators
- Models implement `Authenticatable` for auth (not extending base User)
- Bindings registered in domain-specific service providers (AuthServiceProvider, TenancyServiceProvider)

## App Layer (`app/Http/`)
- Controllers use OpenAPI attributes (`OA\Post`, `OA\Get`, etc.)
- Controllers inject domain ports via constructor DI
- Session-based auth (not token/Sanctum API tokens)
- Session keys: `admin_user_id`, `2fa_pending`, `2fa_verified`
- Auth guard: `admin` (session driver, `admins` provider)
- Middleware aliases in `bootstrap/app.php`: `require.2fa`, `ensure.2fa.setup`

## Routes (`routes/api.php`)
- Central routes wrapped in `foreach (config('tenancy.central_domains')) as $domain) { Route::domain($domain)->group(...) }`
- Auth routes under `/auth/*` prefix
- Rate limiters: `throttle:login` (5/min), `throttle:2fa` (5/5min)
- Protected routes use `['web', 'require.2fa']` middleware stack

## Frontend (`frontend/src/`)
- Two apps: `superAdmin` (central) and `hotelAdmin` (tenant)
- SuperAdmin routes under `/superadmin/*`
- Uses 8bit retro-themed UI components (`components/ui/8bit/`)
- Also has shadcn components (`components/ui/shadcn/`)
- API client: `lib/api.ts` with `createResource<T>` generic helper
- Auth client: `lib/authApi.ts` with CSRF cookie flow
- Types in `types/` directory
- Forms use react-hook-form + zod validation
- State: local useState (no global state management library)

## Database
- Central migrations: `database/migrations/`
- Tenant migrations: `database/migrations/tenant/`
- UUIDs for primary keys everywhere
- Sessions stored in DB (`sessions` table)
- Existing tables: admin_users, tenants, domains, sessions, personal_access_tokens, cache, jobs

## Seeders
- `AdminUserSeeder` creates admin from env vars (ADMIN_EMAIL, ADMIN_PASSWORD)
- Uses `DB::table()->updateOrInsert()` pattern
