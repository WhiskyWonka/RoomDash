<?php

declare(strict_types=1);

use Application\EmailVerification\UseCases\VerifyEmailUseCase;
use Application\EmailVerification\DTOs\VerifyEmailRequest;
use Domain\Auth\Ports\RootUserRepositoryInterface;
use Domain\Auth\Ports\EmailVerificationTokenRepositoryInterface;
use Domain\Auth\Exceptions\ExpiredTokenException;
use Domain\Auth\Exceptions\InvalidTokenException;
use Domain\Auth\ValueObjects\EmailVerificationToken;

it('sets email verified at and password on valid token', function () {
    // Arrange
    $rawToken = 'valid-raw-token';
    $hashedToken = hash('sha256', $rawToken);

    $tokenRecord = new EmailVerificationToken(
        id: 'token-uuid',
        userId: 'user-uuid',
        hashedToken: $hashedToken,
        expiresAt: new DateTimeImmutable('+24 hours'),
        createdAt: new DateTimeImmutable(),
    );

    $tokenRepository = Mockery::mock(EmailVerificationTokenRepositoryInterface::class);
    $userRepository = Mockery::mock(RootUserRepositoryInterface::class);

    $tokenRepository->shouldReceive('findByHashedToken')->with($hashedToken)->andReturn($tokenRecord);
    $userRepository->shouldReceive('verifyEmail')->with('user-uuid', Mockery::type('string'))->once();
    $tokenRepository->shouldReceive('deleteByUserId')->with('user-uuid')->once();

    $useCase = new VerifyEmailUseCase($tokenRepository, $userRepository);

    $request = new VerifyEmailRequest(
        token: $rawToken,
        password: 'SecurePassword123!',
        passwordConfirmation: 'SecurePassword123!',
    );

    // Act
    $useCase->execute($request);
});

it('consumes token after verification', function () {
    // Arrange
    $rawToken = 'valid-raw-token';
    $hashedToken = hash('sha256', $rawToken);

    $tokenRecord = new EmailVerificationToken(
        id: 'token-uuid',
        userId: 'user-uuid',
        hashedToken: $hashedToken,
        expiresAt: new DateTimeImmutable('+24 hours'),
        createdAt: new DateTimeImmutable(),
    );

    $tokenRepository = Mockery::mock(EmailVerificationTokenRepositoryInterface::class);
    $userRepository = Mockery::mock(RootUserRepositoryInterface::class);

    $tokenRepository->shouldReceive('findByHashedToken')->andReturn($tokenRecord);
    $userRepository->shouldReceive('verifyEmail')->once();
    $tokenRepository->shouldReceive('deleteByUserId')
        ->with('user-uuid')
        ->once(); // Token must be deleted after use

    $useCase = new VerifyEmailUseCase($tokenRepository, $userRepository);

    $request = new VerifyEmailRequest(
        token: $rawToken,
        password: 'SecurePassword123!',
        passwordConfirmation: 'SecurePassword123!',
    );

    // Act
    $useCase->execute($request);
});

it('throws exception for expired token', function () {
    // Arrange
    $rawToken = 'expired-raw-token';
    $hashedToken = hash('sha256', $rawToken);

    $expiredToken = new EmailVerificationToken(
        id: 'token-uuid',
        userId: 'user-uuid',
        hashedToken: $hashedToken,
        expiresAt: new DateTimeImmutable('-1 second'),
        createdAt: new DateTimeImmutable('-25 hours'),
    );

    $tokenRepository = Mockery::mock(EmailVerificationTokenRepositoryInterface::class);
    $userRepository = Mockery::mock(RootUserRepositoryInterface::class);

    $tokenRepository->shouldReceive('findByHashedToken')->with($hashedToken)->andReturn($expiredToken);

    $useCase = new VerifyEmailUseCase($tokenRepository, $userRepository);

    $request = new VerifyEmailRequest(
        token: $rawToken,
        password: 'SecurePassword123!',
        passwordConfirmation: 'SecurePassword123!',
    );

    // Act & Assert
    expect(fn () => $useCase->execute($request))
        ->toThrow(ExpiredTokenException::class, 'Verification token has expired');
});

it('throws exception when token does not exist', function () {
    // Arrange
    $rawToken = 'nonexistent-token';
    $hashedToken = hash('sha256', $rawToken);

    $tokenRepository = Mockery::mock(EmailVerificationTokenRepositoryInterface::class);
    $userRepository = Mockery::mock(RootUserRepositoryInterface::class);

    $tokenRepository->shouldReceive('findByHashedToken')->with($hashedToken)->andReturn(null);

    $useCase = new VerifyEmailUseCase($tokenRepository, $userRepository);

    $request = new VerifyEmailRequest(
        token: $rawToken,
        password: 'SecurePassword123!',
        passwordConfirmation: 'SecurePassword123!',
    );

    // Act & Assert
    expect(fn () => $useCase->execute($request))
        ->toThrow(InvalidTokenException::class);
});
