<?php

declare(strict_types=1);

use Domain\Auth\Ports\UserRepositoryInterface;
use Domain\Auth\Services\LastActiveUserGuard;

it('allows deletion when multiple active users exist', function () {
    // Arrange
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldReceive('countActive')->once()->andReturn(3);

    $guard = new LastActiveUserGuard($repository);

    // Act & Assert -- should not throw
    expect(fn () => $guard->assertCanDelete('some-user-uuid'))->not->toThrow(\Exception::class);
});

it('prevents deletion when only one active user exists', function () {
    // Arrange
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldReceive('countActive')->once()->andReturn(1);

    $guard = new LastActiveUserGuard($repository);

    // Act & Assert
    expect(fn () => $guard->assertCanDelete('some-user-uuid'))
        ->toThrow(\Domain\Auth\Exceptions\LastActiveUserException::class, 'Cannot delete the last active root user');
});

it('allows deactivation when multiple active users exist', function () {
    // Arrange
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldReceive('countActive')->once()->andReturn(2);

    $guard = new LastActiveUserGuard($repository);

    // Act & Assert -- should not throw
    expect(fn () => $guard->assertCanDeactivate('some-user-uuid'))->not->toThrow(\Exception::class);
});

it('prevents deactivation when only one active user exists', function () {
    // Arrange
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldReceive('countActive')->once()->andReturn(1);

    $guard = new LastActiveUserGuard($repository);

    // Act & Assert
    expect(fn () => $guard->assertCanDeactivate('some-user-uuid'))
        ->toThrow(\Domain\Auth\Exceptions\LastActiveUserException::class, 'Cannot deactivate the last active root user');
});
