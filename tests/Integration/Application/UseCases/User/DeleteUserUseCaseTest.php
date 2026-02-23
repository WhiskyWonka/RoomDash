<?php

declare(strict_types=1);

use Application\User\DTOs\DeleteUserRequest;
use Application\User\UseCases\DeleteUserUseCase;
use Domain\Auth\Entities\User;
use Domain\Auth\Exceptions\SelfDeletionException;
use Domain\Auth\Ports\UserRepositoryInterface;
use Domain\Auth\Services\LastActiveUserGuard;

it('deletes user successfully', function () {
    // Arrange
    $targetUser = new User(
        id: 'target-uuid',
        username: new \Domain\Auth\ValueObjects\Username('target'),
        firstName: 'Target',
        lastName: 'User',
        email: 'target@example.com',
        password: 'hashed-password',
        avatarPath: null,
        isActive: true,
        twoFactorEnabled: false,
        emailVerifiedAt: new DateTimeImmutable,
        twoFactorConfirmedAt: null,
        createdAt: new DateTimeImmutable,
    );

    $userRepository = Mockery::mock(UserRepositoryInterface::class);
    $lastActiveGuard = Mockery::mock(LastActiveUserGuard::class);

    $userRepository->shouldReceive('findById')->with('target-uuid')->andReturn($targetUser);
    $lastActiveGuard->shouldReceive('assertCanDelete')->with('target-uuid')->once();
    $userRepository->shouldReceive('delete')->with('target-uuid')->once();

    $useCase = new DeleteUserUseCase($userRepository, $lastActiveGuard);

    $request = new DeleteUserRequest(
        id: 'target-uuid',
        actorId: 'actor-uuid',
        ipAddress: '127.0.0.1',
        userAgent: 'TestAgent/1.0',
    );

    // Act & Assert -- should not throw
    $useCase->execute($request);
});

it('throws exception when deleting own account', function () {
    // Arrange
    $userId = 'user-uuid';

    $user = new User(
        id: $userId,
        username: new \Domain\Auth\ValueObjects\Username('jdoe'),
        firstName: 'John',
        lastName: 'Doe',
        email: 'john@example.com',
        password: 'hashed-password',
        avatarPath: null,
        isActive: true,
        twoFactorEnabled: false,
        emailVerifiedAt: new DateTimeImmutable,
        twoFactorConfirmedAt: null,
        createdAt: new DateTimeImmutable,
    );

    $userRepository = Mockery::mock(UserRepositoryInterface::class);
    $lastActiveGuard = Mockery::mock(LastActiveUserGuard::class);

    $userRepository->shouldReceive('findById')->with($userId)->andReturn($user);

    $useCase = new DeleteUserUseCase($userRepository, $lastActiveGuard);

    $request = new DeleteUserRequest(
        id: $userId,
        actorId: $userId, // same ID = self-deletion
        ipAddress: '127.0.0.1',
        userAgent: 'TestAgent/1.0',
    );

    // Act & Assert
    expect(fn () => $useCase->execute($request))
        ->toThrow(SelfDeletionException::class, 'Cannot delete your own account');
});

it('calls delete repository with correct id', function () {
    // Arrange
    $targetUser = new User(
        id: 'target-uuid',
        username: new \Domain\Auth\ValueObjects\Username('target'),
        firstName: 'Target',
        lastName: 'User',
        email: 'target@example.com',
        password: 'hashed-password',
        avatarPath: null,
        isActive: true,
        twoFactorEnabled: false,
        emailVerifiedAt: new DateTimeImmutable,
        twoFactorConfirmedAt: null,
        createdAt: new DateTimeImmutable,
    );

    $userRepository = Mockery::mock(UserRepositoryInterface::class);
    $lastActiveGuard = Mockery::mock(LastActiveUserGuard::class);

    $userRepository->shouldReceive('findById')->with('target-uuid')->andReturn($targetUser);
    $lastActiveGuard->shouldReceive('assertCanDelete')->once();
    $userRepository->shouldReceive('delete')
        ->once()
        ->with('target-uuid');

    $useCase = new DeleteUserUseCase($userRepository, $lastActiveGuard);

    $request = new DeleteUserRequest(
        id: 'target-uuid',
        actorId: 'actor-uuid',
        ipAddress: '127.0.0.1',
        userAgent: 'TestAgent/1.0',
    );

    // Act
    $useCase->execute($request);
});
