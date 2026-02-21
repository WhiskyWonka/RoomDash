<?php

declare(strict_types=1);

use Domain\Auth\Entities\User;
use Domain\Auth\ValueObjects\Username;

it('creates user with valid data', function () {
    // Arrange
    $id = 'user-uuid-1234';
    $username = new Username('jdoe');
    $email = 'john@example.com';
    $password = 'Hola1lu234!';

    // Act
    $user = new User(
        id: $id,
        username: $username,
        firstName: 'John',
        lastName: 'Doe',
        email: $email,
        password: $password,
        isActive: true,
        avatarPath: null,
        twoFactorEnabled: false,
        emailVerifiedAt: null,
        twoFactorConfirmedAt: null,
        createdAt: new DateTimeImmutable,
    );

    // Assert
    expect($user->id)->toBe($id);
    expect($user->username->value())->toBe('jdoe');
    expect($user->email)->toBe($email);
    expect($user->password)->toBe($password);
    expect($user->firstName)->toBe('John');
    expect($user->lastName)->toBe('Doe');
    expect($user->isActive)->toBeTrue();
    expect($user->avatarPath)->toBe(null);
    expect($user->twoFactorEnabled)->toBeFalse();
    expect($user->emailVerifiedAt)->toBeNull();
});

it('serializes user to json correctly', function () {
    // Arrange
    $verifiedAt = new DateTimeImmutable('2026-01-01 12:00:00');
    $createdAt = new DateTimeImmutable('2026-01-01 10:00:00');

    $user = new User(
        id: 'user-uuid-1234',
        username: new Username('jdoe'),
        firstName: 'John',
        lastName: 'Doe',
        email: 'john@example.com',
        password: 'Hola1lu234!',
        isActive: true,
        avatarPath: null,
        twoFactorEnabled: true,
        emailVerifiedAt: $verifiedAt,
        twoFactorConfirmedAt: null,
        createdAt: $createdAt,
    );

    // Act
    $data = $user->jsonSerialize();

    // Assert
    expect($data)->toHaveKey('id', 'user-uuid-1234');
    expect($data)->toHaveKey('username', 'jdoe');
    expect($data)->toHaveKey('firstName', 'John');
    expect($data)->toHaveKey('lastName', 'Doe');
    expect($data)->toHaveKey('email', 'john@example.com');
    expect($data)->toHaveKey('isActive', true);
    expect($data)->toHaveKey('twoFactorEnabled', true);
    expect($data)->toHaveKey('emailVerifiedAt');
    expect($data)->toHaveKey('createdAt');
});

it('detects active user', function () {
    // Arrange & Act
    $user = new User(
        id: 'user-uuid-1234',
        username: new Username('jdoe'),
        firstName: 'John',
        lastName: 'Doe',
        email: 'john@example.com',
        password: 'Hola1lu234!',
        isActive: true,
        avatarPath: null,
        twoFactorEnabled: false,
        emailVerifiedAt: null,
        twoFactorConfirmedAt: null,
        createdAt: new DateTimeImmutable,
    );

    // Assert
    expect($user->isActive)->toBeTrue();
});

it('detects inactive user', function () {
    // Arrange & Act
    $user = new User(
        id: 'user-uuid-1234',
        username: new Username('jdoe'),
        firstName: 'John',
        lastName: 'Doe',
        email: 'john@example.com',
        password: 'Hola1lu234!',
        isActive: false,
        avatarPath: null,
        twoFactorEnabled: false,
        emailVerifiedAt: null,
        twoFactorConfirmedAt: null,
        createdAt: new DateTimeImmutable,
    );

    // Assert
    expect($user->isActive)->toBeFalse();
});

it('detects unverified email', function () {
    // Arrange & Act
    $user = new User(
        id: 'user-uuid-1234',
        username: new Username('jdoe'),
        firstName: 'John',
        lastName: 'Doe',
        email: 'john@example.com',
        password: 'Hola1lu234!',
        isActive: true,
        avatarPath: null,
        twoFactorEnabled: false,
        emailVerifiedAt: null,
        twoFactorConfirmedAt: null,
        createdAt: new DateTimeImmutable,
    );

    // Assert
    expect($user->emailVerifiedAt)->toBeNull();
    expect($user->isEmailVerified())->toBeFalse();
});

it('detects verified email', function () {
    // Arrange & Act
    $user = new User(
        id: 'user-uuid-1234',
        username: new Username('jdoe'),
        firstName: 'John',
        lastName: 'Doe',
        email: 'john@example.com',
        password: 'Hola1lu234!',
        isActive: true,
        avatarPath: null,
        twoFactorEnabled: false,
        emailVerifiedAt: new DateTimeImmutable('2026-01-01'),
        twoFactorConfirmedAt: null,
        createdAt: new DateTimeImmutable,
    );

    // Assert
    expect($user->isEmailVerified())->toBeTrue();
});

it('prevents self deletion', function () {
    // Arrange
    $userId = 'user-uuid-1234';

    $user = new User(
        id: $userId,
        username: new Username('jdoe'),
        firstName: 'John',
        lastName: 'Doe',
        email: 'john@example.com',
        password: 'Hola1lu234!',
        isActive: true,
        avatarPath: null,
        twoFactorEnabled: false,
        emailVerifiedAt: new DateTimeImmutable,
        twoFactorConfirmedAt: null,
        createdAt: new DateTimeImmutable,
    );

    // Act & Assert
    expect(fn () => $user->assertCannotDeleteSelf($userId))
        ->toThrow(\Domain\Auth\Exceptions\SelfDeletionException::class, 'Cannot delete your own account');
});

it('allows deleting a different user', function () {
    // Arrange
    $actorId = 'actor-uuid-1234';

    $targetUser = new User(
        id: 'target-uuid-5678',
        username: new Username('target'),
        firstName: 'Target',
        lastName: 'User',
        email: 'target@example.com',
        password: 'Hola1lu234!',
        isActive: true,
        avatarPath: null,
        twoFactorEnabled: false,
        emailVerifiedAt: new DateTimeImmutable,
        twoFactorConfirmedAt: null,
        createdAt: new DateTimeImmutable,
    );

    // Act & Assert -- should not throw
    expect(fn () => $targetUser->assertCannotDeleteSelf($actorId))->not->toThrow(\Exception::class);
});
