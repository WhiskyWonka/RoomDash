<?php

declare(strict_types=1);

use Application\RootUser\DTOs\CreateRootUserRequest;
use Application\RootUser\UseCases\CreateRootUserUseCase;
use Domain\Auth\Entities\RootUser;
use Domain\Auth\Exceptions\DuplicateEmailException;
use Domain\Auth\Exceptions\DuplicateUsernameException;
use Domain\Auth\Ports\EmailVerificationServiceInterface;
use Domain\Auth\Ports\RootUserRepositoryInterface;
use Domain\Shared\Ports\PasswordHasherInterface;
use Domain\Shared\Ports\UuidGeneratorInterface;

it('creates root user and sends verification email', function () {
    // Arrange
    $userRepository = Mockery::mock(RootUserRepositoryInterface::class);
    $emailService = Mockery::mock(EmailVerificationServiceInterface::class);
    $uuidGenerator = Mockery::mock(UuidGeneratorInterface::class);
    $passwordHasher = Mockery::mock(PasswordHasherInterface::class);

    $userRepository->shouldReceive('existsByEmail')->with('john@example.com')->andReturn(false);
    $userRepository->shouldReceive('existsByUsername')->with('jdoe')->andReturn(false);
    $passwordHasher->shouldReceive('hash')->once()->andReturn('hashed-password');
    $userRepository->shouldReceive('create')->once()->andReturn(
        new RootUser(
            id: 'new-user-uuid',
            username: new \Domain\Auth\ValueObjects\Username('jdoe'),
            firstName: 'John',
            lastName: 'Doe',
            email: 'john@example.com',
            password: 'hashed-password',
            avatarPath: null,
            isActive: true,
            twoFactorEnabled: false,
            emailVerifiedAt: null,
            twoFactorConfirmedAt: null,
            createdAt: new DateTimeImmutable,
        )
    );

    $emailService->shouldReceive('sendVerificationEmail')->with('new-user-uuid')->once();

    $useCase = new CreateRootUserUseCase($userRepository, $emailService, $uuidGenerator, $passwordHasher);

    $request = new CreateRootUserRequest(
        username: 'jdoe',
        firstName: 'John',
        lastName: 'Doe',
        email: 'john@example.com',
        actorId: 'actor-uuid',
        password: 'Password123!',
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
    $uuidGenerator = Mockery::mock(UuidGeneratorInterface::class);
    $passwordHasher = Mockery::mock(PasswordHasherInterface::class);

    $userRepository->shouldReceive('existsByEmail')->with('existing@example.com')->andReturn(true);

    $useCase = new CreateRootUserUseCase($userRepository, $emailService, $uuidGenerator, $passwordHasher);

    $request = new CreateRootUserRequest(
        username: 'jdoe',
        firstName: 'John',
        lastName: 'Doe',
        email: 'existing@example.com',
        actorId: 'actor-uuid',
        password: 'Password123!',
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
    $uuidGenerator = Mockery::mock(UuidGeneratorInterface::class);
    $passwordHasher = Mockery::mock(PasswordHasherInterface::class);

    $userRepository->shouldReceive('existsByEmail')->andReturn(false);
    $userRepository->shouldReceive('existsByUsername')->with('existinguser')->andReturn(true);

    $useCase = new CreateRootUserUseCase($userRepository, $emailService, $uuidGenerator, $passwordHasher);

    $request = new CreateRootUserRequest(
        username: 'existinguser',
        firstName: 'John',
        lastName: 'Doe',
        email: 'new@example.com',
        actorId: 'actor-uuid',
        password: 'Password123!',
        ipAddress: '127.0.0.1',
        userAgent: 'TestAgent/1.0',
    );

    // Act & Assert
    expect(fn () => $useCase->execute($request))
        ->toThrow(DuplicateUsernameException::class);
});

it('sends verification email after creating user', function () {
    // Arrange
    $userRepository = Mockery::mock(RootUserRepositoryInterface::class);
    $emailService = Mockery::mock(EmailVerificationServiceInterface::class);
    $uuidGenerator = Mockery::mock(UuidGeneratorInterface::class);
    $passwordHasher = Mockery::mock(PasswordHasherInterface::class);

    $userRepository->shouldReceive('existsByEmail')->andReturn(false);
    $userRepository->shouldReceive('existsByUsername')->andReturn(false);
    $passwordHasher->shouldReceive('hash')->once()->andReturn('hashed-password');
    $userRepository->shouldReceive('create')->andReturn(
        new RootUser(
            id: 'new-user-uuid',
            username: new \Domain\Auth\ValueObjects\Username('jdoe'),
            firstName: 'John',
            lastName: 'Doe',
            email: 'john@example.com',
            password: 'hashed-password',
            avatarPath: null,
            isActive: true,
            twoFactorEnabled: false,
            emailVerifiedAt: null,
            twoFactorConfirmedAt: null,
            createdAt: new DateTimeImmutable,
        )
    );

    $emailService->shouldReceive('sendVerificationEmail')
        ->once()
        ->with('new-user-uuid');

    $useCase = new CreateRootUserUseCase($userRepository, $emailService, $uuidGenerator, $passwordHasher);

    $request = new CreateRootUserRequest(
        username: 'jdoe',
        firstName: 'John',
        lastName: 'Doe',
        email: 'john@example.com',
        actorId: 'actor-uuid',
        password: 'Password123!',
        ipAddress: '127.0.0.1',
        userAgent: 'TestAgent/1.0',
    );

    // Act
    $useCase->execute($request);

    // Assert -- verified by mock expectation above
});
