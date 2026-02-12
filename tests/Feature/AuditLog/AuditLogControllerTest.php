<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Infrastructure\AuditLog\Models\AuditLog;
use Tests\Helpers\ActsAsAuthenticatedRootUser;

uses(RefreshDatabase::class, ActsAsAuthenticatedRootUser::class);

// =========================================================================
// Authorization
// =========================================================================

it('returns 401 when unauthenticated accessing audit logs', function () {
    // Act
    $response = $this->getJson('/api/audit-logs');

    // Assert
    $response->assertStatus(401);
});

it('returns 403 with 2FA_REQUIRED when 2fa not verified for audit logs', function () {
    // Arrange
    $this->actingAsRootUserPending2FA();

    // Act
    $response = $this->getJson('/api/audit-logs');

    // Assert
    $response->assertStatus(403)
        ->assertJson(['code' => '2FA_REQUIRED']);
});

// =========================================================================
// List (GET /api/audit-logs)
// =========================================================================

it('returns 200 with paginated audit logs', function () {
    // Arrange
    $this->actingAsVerifiedRootUser();
    AuditLog::factory()->count(5)->create();

    // Act
    $response = $this->getJson('/api/audit-logs?page=1&per_page=25');

    // Assert
    $response->assertStatus(200)
        ->assertJsonStructure([
            'data',
            'meta' => ['current_page', 'per_page', 'total'],
        ]);
});

it('returns audit logs newest first', function () {
    // Arrange
    $this->actingAsVerifiedRootUser();

    $oldLog = AuditLog::factory()->create(['created_at' => now()->subDays(2)]);
    $newLog = AuditLog::factory()->create(['created_at' => now()->subDay()]);

    // Act
    $response = $this->getJson('/api/audit-logs');

    // Assert
    $response->assertStatus(200);
    $data = $response->json('data');
    expect($data[0]['id'])->toBe($newLog->id); // Newest first
    expect($data[1]['id'])->toBe($oldLog->id);
});

it('returns audit logs filtered by user id', function () {
    // Arrange
    $this->actingAsVerifiedRootUser();

    $specificUserId = \Illuminate\Support\Str::uuid()->toString();
    AuditLog::factory()->forUser($specificUserId)->create();
    AuditLog::factory()->create(); // Different user

    // Act
    $response = $this->getJson("/api/audit-logs?user_id={$specificUserId}");

    // Assert
    $response->assertStatus(200);
    $data = $response->json('data');
    expect($data)->toHaveCount(1);
    expect($data[0]['userId'])->toBe($specificUserId);
});

it('returns audit logs filtered by action', function () {
    // Arrange
    $this->actingAsVerifiedRootUser();

    AuditLog::factory()->forAction('root_user.created')->create();
    AuditLog::factory()->forAction('auth.login')->create();

    // Act
    $response = $this->getJson('/api/audit-logs?action=root_user.created');

    // Assert
    $response->assertStatus(200);
    $data = $response->json('data');
    expect($data)->toHaveCount(1);
    expect($data[0]['action'])->toBe('root_user.created');
});

it('returns audit logs filtered by date range', function () {
    // Arrange
    $this->actingAsVerifiedRootUser();

    AuditLog::factory()->create(['created_at' => '2026-01-15 12:00:00']);
    AuditLog::factory()->create(['created_at' => '2026-02-01 12:00:00']); // Outside range

    // Act
    $response = $this->getJson('/api/audit-logs?from=2026-01-01&to=2026-01-31');

    // Assert
    $response->assertStatus(200);
    $data = $response->json('data');
    expect($data)->toHaveCount(1);
});

it('returns audit logs filtered by entity type', function () {
    // Arrange
    $this->actingAsVerifiedRootUser();

    AuditLog::factory()->forEntityType('root_user')->create();
    AuditLog::factory()->forEntityType('tenant')->create();

    // Act
    $response = $this->getJson('/api/audit-logs?entity_type=root_user');

    // Assert
    $response->assertStatus(200);
    $data = $response->json('data');
    expect($data)->toHaveCount(1);
    expect($data[0]['entityType'])->toBe('root_user');
});

// =========================================================================
// Show (GET /api/audit-logs/{id})
// =========================================================================

it('returns 200 for get single audit log', function () {
    // Arrange
    $this->actingAsVerifiedRootUser();
    $log = AuditLog::factory()->create();

    // Act
    $response = $this->getJson("/api/audit-logs/{$log->id}");

    // Assert
    $response->assertStatus(200)
        ->assertJsonFragment(['id' => $log->id]);
});

it('returns 404 when audit log not found', function () {
    // Arrange
    $this->actingAsVerifiedRootUser();

    // Act
    $response = $this->getJson('/api/audit-logs/nonexistent-uuid');

    // Assert
    $response->assertStatus(404);
});

// =========================================================================
// Immutability (BR-010)
// =========================================================================

it('returns 405 for put on audit logs', function () {
    // Arrange
    $this->actingAsVerifiedRootUser();
    $log = AuditLog::factory()->create();

    // Act
    $response = $this->putJson("/api/audit-logs/{$log->id}", []);

    // Assert
    $response->assertStatus(405);
});

it('returns 405 for patch on audit logs', function () {
    // Arrange
    $this->actingAsVerifiedRootUser();
    $log = AuditLog::factory()->create();

    // Act
    $response = $this->patchJson("/api/audit-logs/{$log->id}", []);

    // Assert
    $response->assertStatus(405);
});

it('returns 405 for delete on audit logs', function () {
    // Arrange
    $this->actingAsVerifiedRootUser();
    $log = AuditLog::factory()->create();

    // Act
    $response = $this->deleteJson("/api/audit-logs/{$log->id}");

    // Assert
    $response->assertStatus(405);
});
