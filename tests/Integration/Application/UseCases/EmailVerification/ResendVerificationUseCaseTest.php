<?php

declare(strict_types=1);

use Application\EmailVerification\DTOs\ResendVerificationRequest;
use Application\EmailVerification\UseCases\ResendVerificationUseCase;
use Domain\Auth\Entities\RootUser;
use Domain\Auth\Exceptions\AlreadyVerifiedException;
use Domain\Auth\Ports\EmailVerificationServiceInterface;
use Domain\Auth\Ports\RootUserRepositoryInterface;

it('invalidates previous tokens and sends new email', function () {
    // Arrange
    $unverifiedUser = new RootUser(
        id: 'user-uuid',
        username: new \Domain\Auth\ValueObjects\Username('jdoe'),
        firstName: 'John',
        lastName: 'Doe',
        email: 'john@example.com',
        password: 'hashed-password',
        avatarPath: null,
        isActive: true,
        twoFactorEnabled: false,
        emailVerifiedAt: null, // Not verified
        twoFactorConfirmedAt: null,
        createdAt: new DateTimeImmutable,
    );

    $userRepository = Mockery::mock(RootUserRepositoryInterface::class);
    $emailService = Mockery::mock(EmailVerificationServiceInterface::class);

    $userRepository->shouldReceive('findById')->with('user-uuid')->andReturn($unverifiedUser);
    $emailService->shouldReceive('invalidatePreviousTokens')->with('user-uuid')->once();
    $emailService->shouldReceive('sendVerificationEmail')->with('user-uuid')->once();

    $useCase = new ResendVerificationUseCase($userRepository, $emailService);

    $request = new ResendVerificationRequest(
        userId: 'user-uuid',
        actorId: 'actor-uuid',
    );

    // Act
    $useCase->execute($request);
});

it('throws exception when user already verified', function () {
    // Arrange
    $verifiedUser = new RootUser(
        id: 'user-uuid',
        username: new \Domain\Auth\ValueObjects\Username('jdoe'),
        firstName: 'John',
        lastName: 'Doe',
        email: 'john@example.com',
        password: 'hashed-password',
        avatarPath: null,
        isActive: true,
        twoFactorEnabled: false,
        emailVerifiedAt: new DateTimeImmutable, // Already verified
        twoFactorConfirmedAt: null,
        createdAt: new DateTimeImmutable,
    );

    $userRepository = Mockery::mock(RootUserRepositoryInterface::class);
    $emailService = Mockery::mock(EmailVerificationServiceInterface::class);

    $userRepository->shouldReceive('findById')->with('user-uuid')->andReturn($verifiedUser);

    $useCase = new ResendVerificationUseCase($userRepository, $emailService);

    $request = new ResendVerificationRequest(
        userId: 'user-uuid',
        actorId: 'actor-uuid',
    );

    // Act & Assert
    expect(fn () => $useCase->execute($request))
        ->toThrow(AlreadyVerifiedException::class, 'User has already been verified');
});
