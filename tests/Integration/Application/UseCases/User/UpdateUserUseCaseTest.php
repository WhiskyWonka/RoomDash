<?php

declare(strict_types=1);

use Application\User\DTOs\UpdateUserRequest;
use Application\User\UseCases\UpdateUserUseCase;
use Domain\AuditLog\Ports\AuditLogRepositoryInterface;
use Domain\Auth\Entities\User;
use Domain\Auth\Ports\EmailVerificationServiceInterface;
use Domain\Auth\Ports\UserRepositoryInterface;
use Domain\Shared\Ports\UuidGeneratorInterface;

it('updates user fields', function () {
    // Arrange
    $userRepository = Mockery::mock(UserRepositoryInterface::class);
    $emailService = Mockery::mock(EmailVerificationServiceInterface::class);
    $auditLogRepository = Mockery::mock(AuditLogRepositoryInterface::class);
    $uuidGenerator = Mockery::mock(UuidGeneratorInterface::class);

    $existingUser = new User(
        id: 'user-uuid',
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

    $updatedUser = new User(
        id: 'user-uuid',
        username: new \Domain\Auth\ValueObjects\Username('jdoe'),
        firstName: 'Jane',
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

    $userRepository->shouldReceive('findById')->with('user-uuid')->andReturn($existingUser);
    $userRepository->shouldReceive('existsByEmail')->andReturn(false);
    $userRepository->shouldReceive('existsByUsername')->andReturn(false);
    $userRepository->shouldReceive('update')->once()->andReturn($updatedUser);

    $useCase = new UpdateUserUseCase($userRepository, $emailService, $auditLogRepository, $uuidGenerator);

    $request = new UpdateUserRequest(
        id: 'user-uuid',
        username: 'jdoe',
        firstName: 'Jane',
        lastName: 'Doe',
        email: 'john@example.com',
        actorId: 'actor-uuid',
        ipAddress: '127.0.0.1',
        userAgent: 'TestAgent/1.0',
    );

    // Act
    $result = $useCase->execute($request);

    // Assert
    expect($result->firstName)->toBe('Jane');
});

it('triggers reverification when email changes', function () {
    // Arrange
    $userRepository = Mockery::mock(UserRepositoryInterface::class);
    $emailService = Mockery::mock(EmailVerificationServiceInterface::class);
    $auditLogRepository = Mockery::mock(AuditLogRepositoryInterface::class);
    $uuidGenerator = Mockery::mock(UuidGeneratorInterface::class);

    $existingUser = new User(
        id: 'user-uuid',
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

    $updatedUser = new User(
        id: 'user-uuid',
        username: new \Domain\Auth\ValueObjects\Username('jdoe'),
        firstName: 'John',
        lastName: 'Doe',
        email: 'newemail@example.com',
        password: 'hashed-password',
        avatarPath: null,
        isActive: true,
        twoFactorEnabled: false,
        emailVerifiedAt: null,
        twoFactorConfirmedAt: null,
        createdAt: new DateTimeImmutable,
    );

    $userRepository->shouldReceive('findById')->with('user-uuid')->andReturn($existingUser);
    $userRepository->shouldReceive('existsByEmail')->with('newemail@example.com')->andReturn(false);
    $userRepository->shouldReceive('existsByUsername')->andReturn(false);
    $userRepository->shouldReceive('update')->once()->andReturn($updatedUser);
    $userRepository->shouldReceive('clearEmailVerification')->with('user-uuid')->once();

    $emailService->shouldReceive('sendVerificationEmail')->with('user-uuid')->once();
    $emailService->shouldReceive('invalidatePreviousTokens')->with('user-uuid')->once();

    $useCase = new UpdateUserUseCase($userRepository, $emailService, $auditLogRepository, $uuidGenerator);

    $request = new UpdateUserRequest(
        id: 'user-uuid',
        username: 'jdoe',
        firstName: 'John',
        lastName: 'Doe',
        email: 'newemail@example.com',
        actorId: 'actor-uuid',
        ipAddress: '127.0.0.1',
        userAgent: 'TestAgent/1.0',
    );

    // Act
    $useCase->execute($request);

    // Assert -- verified by mock expectations above
});

it('calls update repository with new field values', function () {
    // Arrange
    $userRepository = Mockery::mock(UserRepositoryInterface::class);
    $emailService = Mockery::mock(EmailVerificationServiceInterface::class);
    $auditLogRepository = Mockery::mock(AuditLogRepositoryInterface::class);
    $uuidGenerator = Mockery::mock(UuidGeneratorInterface::class);

    $existingUser = new User(
        id: 'user-uuid',
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

    $updatedUser = new User(
        id: 'user-uuid',
        username: new \Domain\Auth\ValueObjects\Username('jdoe'),
        firstName: 'Jane',
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

    $userRepository->shouldReceive('findById')->andReturn($existingUser);
    $userRepository->shouldReceive('existsByEmail')->andReturn(false);
    $userRepository->shouldReceive('existsByUsername')->andReturn(false);
    $userRepository->shouldReceive('update')
        ->once()
        ->with('user-uuid', Mockery::on(function ($data) {
            return $data['first_name'] === 'Jane'
                && $data['last_name'] === 'Doe';
        }))
        ->andReturn($updatedUser);

    $useCase = new UpdateUserUseCase($userRepository, $emailService, $auditLogRepository, $uuidGenerator);

    $request = new UpdateUserRequest(
        id: 'user-uuid',
        username: 'jdoe',
        firstName: 'Jane',
        lastName: 'Doe',
        email: 'john@example.com',
        actorId: 'actor-uuid',
        ipAddress: '127.0.0.1',
        userAgent: 'TestAgent/1.0',
    );

    // Act
    $useCase->execute($request);
});
