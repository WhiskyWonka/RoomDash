<?php

declare(strict_types=1);

use Domain\Auth\ValueObjects\EmailVerificationToken;

it('creates valid token', function () {
    // Arrange
    $userId = 'user-uuid-1234';
    $rawToken = 'raw-token-string-abc123';
    $expiresAt = new DateTimeImmutable('+24 hours');

    // Act
    $token = new EmailVerificationToken(
        id: 'token-uuid-1234',
        userId: $userId,
        hashedToken: hash('sha256', $rawToken),
        expiresAt: $expiresAt,
        createdAt: new DateTimeImmutable(),
    );

    // Assert
    expect($token->userId)->toBe($userId);
    expect($token->hashedToken)->toBe(hash('sha256', $rawToken));
});

it('detects non-expired token', function () {
    // Arrange
    $expiresAt = new DateTimeImmutable('+23 hours');

    // Act
    $token = new EmailVerificationToken(
        id: 'token-uuid-1234',
        userId: 'user-uuid-1234',
        hashedToken: 'abc123hash',
        expiresAt: $expiresAt,
        createdAt: new DateTimeImmutable('-1 hour'),
    );

    // Assert
    expect($token->isExpired())->toBeFalse();
});

it('detects expired token', function () {
    // Arrange
    $expiresAt = new DateTimeImmutable('-1 second');

    // Act
    $token = new EmailVerificationToken(
        id: 'token-uuid-1234',
        userId: 'user-uuid-1234',
        hashedToken: 'abc123hash',
        expiresAt: $expiresAt,
        createdAt: new DateTimeImmutable('-25 hours'),
    );

    // Assert
    expect($token->isExpired())->toBeTrue();
});

it('token expired exactly at expiry boundary is expired', function () {
    // Arrange -- token expired 1 second ago
    $expiresAt = new DateTimeImmutable('-1 second');

    // Act
    $token = new EmailVerificationToken(
        id: 'token-uuid-1234',
        userId: 'user-uuid-1234',
        hashedToken: 'abc123hash',
        expiresAt: $expiresAt,
        createdAt: new DateTimeImmutable('-24 hours -1 second'),
    );

    // Assert
    expect($token->isExpired())->toBeTrue();
});
