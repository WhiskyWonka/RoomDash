<?php

declare(strict_types=1);

use Domain\AuditLog\Entities\AuditLog;

it('creates audit log entry with required fields', function () {
    // Arrange
    $id = 'log-uuid-1234';
    $userId = 'user-uuid-1234';
    $action = 'root_user.created';
    $createdAt = new DateTimeImmutable('2026-01-01 12:00:00');

    // Act
    $log = new AuditLog(
        id: $id,
        userId: $userId,
        action: $action,
        entityType: 'root_user',
        entityId: 'new-user-uuid',
        oldValues: null,
        newValues: ['email' => 'john@example.com'],
        ipAddress: '127.0.0.1',
        userAgent: 'Mozilla/5.0',
        createdAt: $createdAt,
    );

    // Assert
    expect($log->id)->toBe($id);
    expect($log->userId)->toBe($userId);
    expect($log->action)->toBe($action);
    expect($log->entityType)->toBe('root_user');
    expect($log->entityId)->toBe('new-user-uuid');
});

it('serializes audit log to json correctly', function () {
    // Arrange
    $createdAt = new DateTimeImmutable('2026-01-01 12:00:00');

    $log = new AuditLog(
        id: 'log-uuid-1234',
        userId: 'user-uuid-1234',
        action: 'root_user.updated',
        entityType: 'root_user',
        entityId: 'target-uuid',
        oldValues: ['first_name' => 'John'],
        newValues: ['first_name' => 'Jane'],
        ipAddress: '192.168.1.1',
        userAgent: 'TestAgent/1.0',
        createdAt: $createdAt,
    );

    // Act
    $data = $log->jsonSerialize();

    // Assert
    expect($data)->toHaveKey('id', 'log-uuid-1234');
    expect($data)->toHaveKey('userId', 'user-uuid-1234');
    expect($data)->toHaveKey('action', 'root_user.updated');
    expect($data)->toHaveKey('entityType', 'root_user');
    expect($data)->toHaveKey('entityId', 'target-uuid');
    expect($data)->toHaveKey('oldValues', ['first_name' => 'John']);
    expect($data)->toHaveKey('newValues', ['first_name' => 'Jane']);
    expect($data)->toHaveKey('ipAddress', '192.168.1.1');
    expect($data)->toHaveKey('userAgent', 'TestAgent/1.0');
    expect($data)->toHaveKey('createdAt');
});

it('creates audit log with old and new values', function () {
    // Arrange
    $oldValues = ['email' => 'old@example.com', 'first_name' => 'John'];
    $newValues = ['email' => 'new@example.com', 'first_name' => 'John'];

    // Act
    $log = new AuditLog(
        id: 'log-uuid-1234',
        userId: 'user-uuid-1234',
        action: 'root_user.updated',
        entityType: 'root_user',
        entityId: 'target-uuid',
        oldValues: $oldValues,
        newValues: $newValues,
        ipAddress: null,
        userAgent: null,
        createdAt: new DateTimeImmutable(),
    );

    // Assert
    expect($log->oldValues)->toBe($oldValues);
    expect($log->newValues)->toBe($newValues);
});

it('creates audit log with null optional fields', function () {
    // Arrange & Act
    $log = new AuditLog(
        id: 'log-uuid-1234',
        userId: 'user-uuid-1234',
        action: 'auth.login',
        entityType: null,
        entityId: null,
        oldValues: null,
        newValues: null,
        ipAddress: null,
        userAgent: null,
        createdAt: new DateTimeImmutable(),
    );

    // Assert
    expect($log->entityType)->toBeNull();
    expect($log->entityId)->toBeNull();
    expect($log->oldValues)->toBeNull();
    expect($log->newValues)->toBeNull();
    expect($log->ipAddress)->toBeNull();
    expect($log->userAgent)->toBeNull();
});

it('truncates user agent to 500 characters', function () {
    // Arrange
    $longUserAgent = str_repeat('A', 600);

    // Act
    $log = new AuditLog(
        id: 'log-uuid-1234',
        userId: 'user-uuid-1234',
        action: 'auth.login',
        entityType: null,
        entityId: null,
        oldValues: null,
        newValues: null,
        ipAddress: null,
        userAgent: $longUserAgent,
        createdAt: new DateTimeImmutable(),
    );

    // Assert
    expect(strlen($log->userAgent))->toBe(500);
});
