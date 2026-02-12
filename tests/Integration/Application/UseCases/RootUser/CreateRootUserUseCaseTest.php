<?php

declare(strict_types=1);

use Application\RootUser\UseCases\CreateRootUserUseCase;
use Application\RootUser\DTOs\CreateRootUserRequest;
use Domain\Auth\Entities\RootUser;
use Domain\Auth\Exceptions\DuplicateEmailException;
use Domain\Auth\Exceptions\DuplicateUsernameException;
use Domain\Auth\Ports\RootUserRepositoryInterface;
use Domain\Auth\Ports\EmailVerificationServiceInterface;
use Domain\AuditLog\Ports\AuditLogRepositoryInterface;

it('creates root user and sends verification email', function () {
    // Arrange
    $userRepository = Mockery::mock(RootUserRepositoryInterface::class);
    $emailService = Mockery::mock(EmailVerificationServiceInterface::class);
    $auditLogRepository = Mockery::mock(AuditLogRepositoryInterface::class);

    $userRepository->shouldReceive('existsByEmail')->with('john@example.com')->andReturn(false);
    $userRepository->shouldReceive('existsByUsername')->with('jdoe')->andReturn(false);
    $userRepository->shouldReceive('create')->once()->andReturn(
        new RootUser(
            id: 'new-user-uuid',
            username: new \Domain\Auth\ValueObjects\Username('jdoe'),
            firstName: 'John',
            lastName: 'Doe',
            email: 'john@example.com',
            isActive: true,
            twoFactorEnabled: false,
            emailVerifiedAt: null,
            twoFactorConfirmedAt: null,
            createdAt: new DateTimeImmutable(),
        )
    );

    $emailService->shouldReceive('sendVerificationEmail')->with('new-user-uuid')->once();
    $auditLogRepository->shouldReceive('create')->once();

    $useCase = new CreateRootUserUseCase($userRepository, $emailService, $auditLogRepository);

    $request = new CreateRootUserRequest(
        username: 'jdoe',
        firstName: 'John',
        lastName: 'Doe',
        email: 'john@example.com',
        actorId: 'actor-uuid',
        ipAddress: '127.0.0.1',
        userAgent: 'TestAgent/1.0',
    );

    // Act
    $result = $useCase->execute($request);

    // Assert
    expect($result->email)->toBe('john@example.com');
    expect($result->id)->toBe('new-user-uuid');
});

it('throws exception when email already exists', function () {
    // Arrange
    $userRepository = Mockery::mock(RootUserRepositoryInterface::class);
    $emailService = Mockery::mock(EmailVerificationServiceInterface::class);
    $auditLogRepository = Mockery::mock(AuditLogRepositoryInterface::class);

    $userRepository->shouldReceive('existsByEmail')->with('existing@example.com')->andReturn(true);

    $useCase = new CreateRootUserUseCase($userRepository, $emailService, $auditLogRepository);

    $request = new CreateRootUserRequest(
        username: 'jdoe',
        firstName: 'John',
        lastName: 'Doe',
        email: 'existing@example.com',
        actorId: 'actor-uuid',
        ipAddress: '127.0.0.1',
        userAgent: 'TestAgent/1.0',
    );

    // Act & Assert
    expect(fn () => $useCase->execute($request))
        ->toThrow(DuplicateEmailException::class);
});

it('throws exception when username already exists', function () {
    // Arrange
    $userRepository = Mockery::mock(RootUserRepositoryInterface::class);
    $emailService = Mockery::mock(EmailVerificationServiceInterface::class);
    $auditLogRepository = Mockery::mock(AuditLogRepositoryInterface::class);

    $userRepository->shouldReceive('existsByEmail')->andReturn(false);
    $userRepository->shouldReceive('existsByUsername')->with('existinguser')->andReturn(true);

    $useCase = new CreateRootUserUseCase($userRepository, $emailService, $auditLogRepository);

    $request = new CreateRootUserRequest(
        username: 'existinguser',
        firstName: 'John',
        lastName: 'Doe',
        email: 'new@example.com',
        actorId: 'actor-uuid',
        ipAddress: '127.0.0.1',
        userAgent: 'TestAgent/1.0',
    );

    // Act & Assert
    expect(fn () => $useCase->execute($request))
        ->toThrow(DuplicateUsernameException::class);
});

it('records audit log entry on creation', function () {
    // Arrange
    $userRepository = Mockery::mock(RootUserRepositoryInterface::class);
    $emailService = Mockery::mock(EmailVerificationServiceInterface::class);
    $auditLogRepository = Mockery::mock(AuditLogRepositoryInterface::class);

    $userRepository->shouldReceive('existsByEmail')->andReturn(false);
    $userRepository->shouldReceive('existsByUsername')->andReturn(false);
    $userRepository->shouldReceive('create')->andReturn(
        new RootUser(
            id: 'new-user-uuid',
            username: new \Domain\Auth\ValueObjects\Username('jdoe'),
            firstName: 'John',
            lastName: 'Doe',
            email: 'john@example.com',
            isActive: true,
            twoFactorEnabled: false,
            emailVerifiedAt: null,
            twoFactorConfirmedAt: null,
            createdAt: new DateTimeImmutable(),
        )
    );

    $emailService->shouldReceive('sendVerificationEmail')->once();

    $auditLogRepository->shouldReceive('create')
        ->once()
        ->with(Mockery::on(function ($auditLog) {
            return $auditLog->action === 'root_user.created'
                && $auditLog->entityType === 'root_user';
        }));

    $useCase = new CreateRootUserUseCase($userRepository, $emailService, $auditLogRepository);

    $request = new CreateRootUserRequest(
        username: 'jdoe',
        firstName: 'John',
        lastName: 'Doe',
        email: 'john@example.com',
        actorId: 'actor-uuid',
        ipAddress: '127.0.0.1',
        userAgent: 'TestAgent/1.0',
    );

    // Act
    $useCase->execute($request);

    // Assert -- verified by mock expectation above
});
