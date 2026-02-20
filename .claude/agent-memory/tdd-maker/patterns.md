# TDD Patterns - RoomDash

## Feature Test Auth Helper Pattern
Use the `ActsAsAuthenticatedRootUser` trait in feature tests:
```php
uses(RefreshDatabase::class, ActsAsAuthenticatedRootUser::class);

it('test name', function () {
    $actor = $this->actingAsVerifiedRootUser(); // Full 2FA verified session
    // or
    $this->actingAsRootUserPending2FA(); // Auth'd but 2FA not done
});
```

## Integration Test Mockery Pattern
```php
use Domain\Auth\Ports\RootUserRepositoryInterface;

it('test name', function () {
    $repository = Mockery::mock(RootUserRepositoryInterface::class);
    $repository->shouldReceive('methodName')->with(...)->andReturn(...);
    // ...
});

// In Pest.php:
afterEach(function () { Mockery::close(); });
```

## Factory States Pattern
```php
RootUser::factory()->create();          // Active, verified, with password
RootUser::factory()->unverified()->create(); // No password, no email_verified_at
RootUser::factory()->inactive()->create();   // is_active = false
RootUser::factory()->withTwoFactor()->create();
RootUser::factory()->withAvatar()->create();

EmailVerificationToken::factory()->expired()->withRawToken($raw)->create([...]);
AuditLog::factory()->forAction('root_user.created')->forUser($userId)->create();
```

## Domain Exception Naming
- `InvalidUsernameException` - username format invalid
- `SelfDeletionException` - user tries to delete self
- `LastActiveUserException` - cannot delete/deactivate last active user
- `DuplicateEmailException` - email already in use
- `DuplicateUsernameException` - username already in use
- `ExpiredTokenException` - verification token expired
- `InvalidTokenException` - token not found / already consumed
- `AlreadyVerifiedException` - user already email-verified

## Audit Log Actions
Standard action strings:
- `root_user.created`, `root_user.updated`, `root_user.deleted`
- `root_user.deactivated`, `root_user.activated`
- `root_user.avatar_updated`
- `auth.login`, `auth.logout`
- `tenant.created`, `tenant.updated`, `tenant.deleted`
