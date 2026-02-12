<?php

declare(strict_types=1);

use Domain\Auth\ValueObjects\Username;

it('creates valid username', function () {
    // Arrange
    $rawValue = 'john_doe';

    // Act
    $username = new Username($rawValue);

    // Assert
    expect($username->value())->toBe('john_doe');
});

it('allows username with underscores and hyphens', function () {
    // Arrange
    $rawValue = 'john-doe_123';

    // Act
    $username = new Username($rawValue);

    // Assert
    expect($username->value())->toBe('john-doe_123');
});

it('allows username with alphanumeric characters only', function () {
    // Arrange
    $rawValue = 'JohnDoe99';

    // Act
    $username = new Username($rawValue);

    // Assert
    expect($username->value())->toBe('JohnDoe99');
});

it('throws exception when username contains spaces', function () {
    // Act & Assert
    expect(fn () => new Username('john doe'))
        ->toThrow(\Domain\Auth\Exceptions\InvalidUsernameException::class, 'Username cannot contain spaces');
});

it('throws exception when username is empty', function () {
    // Act & Assert
    expect(fn () => new Username(''))
        ->toThrow(\Domain\Auth\Exceptions\InvalidUsernameException::class);
});

it('throws exception when username has invalid characters', function () {
    // Act & Assert
    expect(fn () => new Username('john@doe'))
        ->toThrow(\Domain\Auth\Exceptions\InvalidUsernameException::class);
});

it('throws exception when username has special characters', function () {
    // Act & Assert
    expect(fn () => new Username('john.doe'))
        ->toThrow(\Domain\Auth\Exceptions\InvalidUsernameException::class);
});
