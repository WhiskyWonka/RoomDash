<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Infrastructure\Auth\Models\RootUser;
use Infrastructure\AuditLog\Models\AuditLog;
use Tests\Helpers\ActsAsAuthenticatedRootUser;

uses(RefreshDatabase::class, ActsAsAuthenticatedRootUser::class);

// =========================================================================
// Authorization
// =========================================================================

it('returns 401 when unauthenticated accessing root users', function () {
    // Act
    $response = $this->getJson('/api/root-users');

    // Assert
    $response->assertStatus(401);
});

it('returns 403 with 2FA_REQUIRED when 2fa not verified', function () {
    // Arrange
    $this->actingAsRootUserPending2FA();

    // Act
    $response = $this->getJson('/api/root-users');

    // Assert
    $response->assertStatus(403)
        ->assertJson(['code' => '2FA_REQUIRED']);
});

// =========================================================================
// List (GET /api/root-users)
// =========================================================================

it('returns 200 with paginated list of root users', function () {
    // Arrange
    $actor = $this->actingAsVerifiedRootUser();
    RootUser::factory()->count(3)->create();

    // Act
    $response = $this->getJson('/api/root-users');

    // Assert
    $response->assertStatus(200)
        ->assertJsonStructure([
            'data',
            'meta' => ['current_page', 'per_page', 'total'],
        ]);
});

it('returns correct fields in root user list', function () {
    // Arrange
    $actor = $this->actingAsVerifiedRootUser();

    // Act
    $response = $this->getJson('/api/root-users');

    // Assert
    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'username',
                    'firstName',
                    'lastName',
                    'email',
                    'avatarUrl',
                    'isActive',
                    'emailVerifiedAt',
                    'twoFactorEnabled',
                    'createdAt',
                ],
            ],
        ]);
});

// =========================================================================
// Show (GET /api/root-users/{id})
// =========================================================================

it('returns 200 with root user data', function () {
    // Arrange
    $actor = $this->actingAsVerifiedRootUser();
    $target = RootUser::factory()->create();

    // Act
    $response = $this->getJson("/api/root-users/{$target->id}");

    // Assert
    $response->assertStatus(200)
        ->assertJsonFragment(['id' => $target->id]);
});

it('returns 404 when root user not found', function () {
    // Arrange
    $this->actingAsVerifiedRootUser();

    // Act
    $response = $this->getJson('/api/root-users/nonexistent-uuid');

    // Assert
    $response->assertStatus(404);
});

// =========================================================================
// Create (POST /api/root-users)
// =========================================================================

it('returns 201 when creating root user with valid data', function () {
    // Arrange
    Mail::fake();
    $this->actingAsVerifiedRootUser();

    $payload = [
        'username' => 'newuser',
        'first_name' => 'New',
        'last_name' => 'User',
        'email' => 'newuser@example.com',
    ];

    // Act
    $response = $this->postJson('/api/root-users', $payload);

    // Assert
    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => ['id', 'username', 'firstName', 'lastName', 'email', 'isActive', 'emailVerifiedAt'],
        ]);
    $this->assertDatabaseHas('root_users', ['email' => 'newuser@example.com']);
});

it('returns 422 when email already exists', function () {
    // Arrange
    $this->actingAsVerifiedRootUser();
    RootUser::factory()->create(['email' => 'existing@example.com']);

    // Act
    $response = $this->postJson('/api/root-users', [
        'username' => 'newuser',
        'first_name' => 'New',
        'last_name' => 'User',
        'email' => 'existing@example.com',
    ]);

    // Assert
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('returns 422 when username already exists', function () {
    // Arrange
    $this->actingAsVerifiedRootUser();
    RootUser::factory()->create(['username' => 'existinguser']);

    // Act
    $response = $this->postJson('/api/root-users', [
        'username' => 'existinguser',
        'first_name' => 'New',
        'last_name' => 'User',
        'email' => 'newemail@example.com',
    ]);

    // Assert
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['username']);
});

it('returns 422 when username contains spaces', function () {
    // Arrange
    $this->actingAsVerifiedRootUser();

    // Act
    $response = $this->postJson('/api/root-users', [
        'username' => 'john doe',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
    ]);

    // Assert
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['username']);
});

it('returns 422 when required fields are missing', function () {
    // Arrange
    $this->actingAsVerifiedRootUser();

    // Act
    $response = $this->postJson('/api/root-users', []);

    // Assert
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['username', 'first_name', 'last_name', 'email']);
});

it('records audit log when root user created', function () {
    // Arrange
    Mail::fake();
    $actor = $this->actingAsVerifiedRootUser();

    // Act
    $this->postJson('/api/root-users', [
        'username' => 'newuser',
        'first_name' => 'New',
        'last_name' => 'User',
        'email' => 'newuser@example.com',
    ]);

    // Assert
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $actor->id,
        'action' => 'root_user.created',
        'entity_type' => 'root_user',
    ]);
});

// =========================================================================
// Update (PUT /api/root-users/{id})
// =========================================================================

it('returns 200 when updating root user with valid data', function () {
    // Arrange
    $actor = $this->actingAsVerifiedRootUser();
    $target = RootUser::factory()->create([
        'username' => 'targetuser',
        'first_name' => 'Target',
        'last_name' => 'User',
        'email' => 'target@example.com',
    ]);

    // Act
    $response = $this->putJson("/api/root-users/{$target->id}", [
        'username' => 'targetuser',
        'first_name' => 'Updated',
        'last_name' => 'Name',
        'email' => 'target@example.com',
    ]);

    // Assert
    $response->assertStatus(200)
        ->assertJsonFragment(['firstName' => 'Updated']);
});

it('records audit log when root user updated', function () {
    // Arrange
    $actor = $this->actingAsVerifiedRootUser();
    $target = RootUser::factory()->create();

    // Act
    $this->putJson("/api/root-users/{$target->id}", [
        'username' => $target->username,
        'first_name' => 'UpdatedName',
        'last_name' => $target->last_name,
        'email' => $target->email,
    ]);

    // Assert
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $actor->id,
        'action' => 'root_user.updated',
        'entity_type' => 'root_user',
        'entity_id' => $target->id,
    ]);
});

// =========================================================================
// Delete (DELETE /api/root-users/{id})
// =========================================================================

it('returns 204 when deleting another root user', function () {
    // Arrange
    $actor = $this->actingAsVerifiedRootUser();
    $target = RootUser::factory()->create();

    // Act
    $response = $this->deleteJson("/api/root-users/{$target->id}");

    // Assert
    $response->assertStatus(204);
    $this->assertDatabaseMissing('root_users', ['id' => $target->id]);
});

it('returns 403 when deleting own account', function () {
    // Arrange
    $actor = $this->actingAsVerifiedRootUser();

    // Act
    $response = $this->deleteJson("/api/root-users/{$actor->id}");

    // Assert
    $response->assertStatus(403)
        ->assertJson(['message' => 'Cannot delete your own account']);
});

it('returns 409 when deleting last active root user', function () {
    // Arrange
    $actor = $this->actingAsVerifiedRootUser();
    $target = RootUser::factory()->create(['is_active' => true]);

    // Make actor inactive so target is the only active one
    $actor->update(['is_active' => false]);

    // Act
    $response = $this->deleteJson("/api/root-users/{$target->id}");

    // Assert
    $response->assertStatus(409)
        ->assertJson(['message' => 'Cannot delete the last active root user']);
});

it('records audit log when root user deleted', function () {
    // Arrange
    $actor = $this->actingAsVerifiedRootUser();
    $target = RootUser::factory()->create();

    // Act
    $this->deleteJson("/api/root-users/{$target->id}");

    // Assert
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $actor->id,
        'action' => 'root_user.deleted',
        'entity_type' => 'root_user',
        'entity_id' => $target->id,
    ]);
});

// =========================================================================
// Activation / Deactivation
// =========================================================================

it('returns 200 when deactivating root user', function () {
    // Arrange
    $actor = $this->actingAsVerifiedRootUser();
    $target = RootUser::factory()->create(['is_active' => true]);

    // Act
    $response = $this->patchJson("/api/root-users/{$target->id}/deactivate");

    // Assert
    $response->assertStatus(200);
    $this->assertDatabaseHas('root_users', ['id' => $target->id, 'is_active' => false]);
});

it('records audit log when root user deactivated', function () {
    // Arrange
    $actor = $this->actingAsVerifiedRootUser();
    $target = RootUser::factory()->create(['is_active' => true]);

    // Act
    $this->patchJson("/api/root-users/{$target->id}/deactivate");

    // Assert
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $actor->id,
        'action' => 'root_user.deactivated',
        'entity_type' => 'root_user',
        'entity_id' => $target->id,
    ]);
});

it('returns 200 when activating root user', function () {
    // Arrange
    $actor = $this->actingAsVerifiedRootUser();
    $target = RootUser::factory()->create(['is_active' => false]);

    // Act
    $response = $this->patchJson("/api/root-users/{$target->id}/activate");

    // Assert
    $response->assertStatus(200);
    $this->assertDatabaseHas('root_users', ['id' => $target->id, 'is_active' => true]);
});

it('records audit log when root user activated', function () {
    // Arrange
    $actor = $this->actingAsVerifiedRootUser();
    $target = RootUser::factory()->create(['is_active' => false]);

    // Act
    $this->patchJson("/api/root-users/{$target->id}/activate");

    // Assert
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $actor->id,
        'action' => 'root_user.activated',
        'entity_type' => 'root_user',
        'entity_id' => $target->id,
    ]);
});

it('returns 409 when deactivating last active root user', function () {
    // Arrange
    $actor = $this->actingAsVerifiedRootUser();

    // Make actor the only active user
    RootUser::where('id', '!=', $actor->id)->update(['is_active' => false]);

    // Act
    $response = $this->patchJson("/api/root-users/{$actor->id}/deactivate");

    // Assert
    $response->assertStatus(409)
        ->assertJson(['message' => 'Cannot deactivate the last active root user']);
});

// =========================================================================
// Resend Verification
// =========================================================================

it('returns 200 when resending verification email', function () {
    // Arrange
    Mail::fake();
    $actor = $this->actingAsVerifiedRootUser();
    $unverified = RootUser::factory()->unverified()->create();

    // Act
    $response = $this->postJson("/api/root-users/{$unverified->id}/resend-verification");

    // Assert
    $response->assertStatus(200);
});

it('invalidates previous tokens when resending verification', function () {
    // Arrange
    Mail::fake();
    $actor = $this->actingAsVerifiedRootUser();
    $unverified = RootUser::factory()->unverified()->create();

    // Create an existing token for this user
    \Infrastructure\Auth\Models\EmailVerificationToken::factory()->create([
        'user_id' => $unverified->id,
    ]);

    // Act
    $this->postJson("/api/root-users/{$unverified->id}/resend-verification");

    // Assert -- old tokens for this user are gone; new one created
    $this->assertDatabaseCount('email_verification_tokens', 1); // Only the newly created one
});

it('returns 409 when resending verification for already verified user', function () {
    // Arrange
    $actor = $this->actingAsVerifiedRootUser();
    $verified = RootUser::factory()->create(['email_verified_at' => now()]);

    // Act
    $response = $this->postJson("/api/root-users/{$verified->id}/resend-verification");

    // Assert
    $response->assertStatus(409)
        ->assertJson(['message' => 'User has already been verified']);
});

// =========================================================================
// Avatar
// =========================================================================

it('returns 200 when uploading valid avatar', function () {
    // Arrange
    \Illuminate\Support\Facades\Storage::fake('public');
    $actor = $this->actingAsVerifiedRootUser();
    $target = RootUser::factory()->create();

    $file = \Illuminate\Http\UploadedFile::fake()->image('avatar.jpg', 200, 200);

    // Act
    $response = $this->postJson("/api/root-users/{$target->id}/avatar", [
        'avatar' => $file,
    ]);

    // Assert
    $response->assertStatus(200)
        ->assertJsonStructure(['data' => ['avatarUrl']]);
});

it('records audit log when avatar uploaded', function () {
    // Arrange
    \Illuminate\Support\Facades\Storage::fake('public');
    $actor = $this->actingAsVerifiedRootUser();
    $target = RootUser::factory()->create();

    $file = \Illuminate\Http\UploadedFile::fake()->image('avatar.jpg', 200, 200);

    // Act
    $this->postJson("/api/root-users/{$target->id}/avatar", [
        'avatar' => $file,
    ]);

    // Assert
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $actor->id,
        'action' => 'root_user.avatar_updated',
        'entity_type' => 'root_user',
        'entity_id' => $target->id,
    ]);
});

it('returns 422 when avatar exceeds 1mb', function () {
    // Arrange
    \Illuminate\Support\Facades\Storage::fake('public');
    $this->actingAsVerifiedRootUser();
    $target = RootUser::factory()->create();

    $file = \Illuminate\Http\UploadedFile::fake()->create('avatar.jpg', 1025, 'image/jpeg'); // 1025 KB > 1MB

    // Act
    $response = $this->postJson("/api/root-users/{$target->id}/avatar", [
        'avatar' => $file,
    ]);

    // Assert
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['avatar']);
});

it('returns 422 when avatar format is invalid', function () {
    // Arrange
    \Illuminate\Support\Facades\Storage::fake('public');
    $this->actingAsVerifiedRootUser();
    $target = RootUser::factory()->create();

    $file = \Illuminate\Http\UploadedFile::fake()->create('avatar.gif', 100, 'image/gif');

    // Act
    $response = $this->postJson("/api/root-users/{$target->id}/avatar", [
        'avatar' => $file,
    ]);

    // Assert
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['avatar']);
});

it('returns 200 when deleting avatar', function () {
    // Arrange
    \Illuminate\Support\Facades\Storage::fake('public');
    $actor = $this->actingAsVerifiedRootUser();
    $target = RootUser::factory()->withAvatar()->create();

    // Act
    $response = $this->deleteJson("/api/root-users/{$target->id}/avatar");

    // Assert
    $response->assertStatus(200);
    $this->assertDatabaseHas('root_users', ['id' => $target->id, 'avatar_path' => null]);
});

it('returns 404 when uploading avatar for non existent user', function () {
    // Arrange
    \Illuminate\Support\Facades\Storage::fake('public');
    $this->actingAsVerifiedRootUser();

    $file = \Illuminate\Http\UploadedFile::fake()->image('avatar.jpg', 200, 200);

    // Act
    $response = $this->postJson('/api/root-users/nonexistent-uuid/avatar', [
        'avatar' => $file,
    ]);

    // Assert
    $response->assertStatus(404);
});
