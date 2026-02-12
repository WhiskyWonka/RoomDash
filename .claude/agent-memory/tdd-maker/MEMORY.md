# TDD Agent Memory - RoomDash Project

## Project Architecture
- Hexagonal architecture: Domain / Application / Infrastructure / App\Http
- PSR-4: `Domain\`, `Application\`, `Infrastructure\`, `App\`
- Multi-tenant with stancl/tenancy (PostgreSQL schema-per-tenant)
- Tests use Pest (not PHPUnit directly)
- Docker/Sail required for feature tests (container: `roomdash-laravel.test-1`)

## Test Infrastructure
- Feature tests: extend `Tests\TestCase` (via Pest.php), use `RefreshDatabase`
- Integration tests: extend `Tests\TestCase`, use Mockery (close in `afterEach`)
- Unit tests: plain Pest, no Laravel bootstrap needed
- Run tests in Docker: `docker exec roomdash-laravel.test-1 php /var/www/html/vendor/bin/pest`
- Or via Sail: `./vendor/bin/sail exec laravel.test php vendor/bin/pest`

## Existing Auth System
- `AdminUser` entity/model/repo/controller/middleware -- being replaced by `RootUser` in TASK-0000
- Login uses session keys: `admin_user_id`, `2fa_verified`, `2fa_pending`
- Auth guard name: `admin`
- Middleware `Require2FA` checks `admin_user_id` + `2fa_verified` in session

## Patterns Confirmed
- Factories live in `database/factories/`, must use FQCN for `$model`
- `ActsAsAuthenticatedRootUser` trait: sets session directly via `withSession()`
- Builders use clone pattern (immutable) in `tests/Builders/`
- Domain entities are readonly immutable, use `DateTimeImmutable` (not Carbon)
- Eloquent models have `toEntity()` method converting to domain entity

## Key File Paths
- Domain entities: `app/Domain/{Context}/Entities/`
- Domain ports: `app/Domain/{Context}/Ports/`
- Infrastructure adapters: `app/Infrastructure/{Context}/Adapters/`
- Infrastructure models: `app/Infrastructure/{Context}/Models/`
- Service providers: `app/Infrastructure/{Context}/Providers/`
- Central routes: `routes/api.php`
- Tenant routes: `routes/tenant.php`

## Pest.php Configuration
- `->in('Feature')` uses `Tests\TestCase`
- `->in('Integration')` also uses `Tests\TestCase`
- `afterEach(fn() => Mockery::close())` added for integration tests

See `patterns.md` for detailed test patterns.
