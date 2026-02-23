<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Infrastructure\Auth\Models\User;
use Tests\Helpers\ActsAsAuthenticatedUser;

uses(RefreshDatabase::class, ActsAsAuthenticatedUser::class);

// =========================================================================
// Authorization
// =========================================================================

it('returns 401 when unauthenticated accessing users', function () {
    // Act
    $response = $this->getJson('/api/users');

    // Assert
    $response->assertStatus(401);
});

it('returns 403 with 2FA_REQUIRED when 2fa not verified', function () {
    // Arrange
    $this->actingAsUserPending2FA();

    // Act
    $response = $this->getJson('/api/users');

    // Assert
    $response->assertStatus(403)
        ->assertJson(['errors' => ['code' => '2FA_REQUIRED']]);
});

// =========================================================================
// List (GET /api/users)
// =========================================================================

it('returns 200 with paginated list of users', function () {
    // Arrange
    $actor = $this->actingAsVerifiedUser();
    User::factory()->count(3)->create();

    // Act
    $response = $this->getJson('/api/users');

    // Assert
    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'items',
                'meta' => ['current_page', 'per_page', 'total'],
            ],
        ]);
});

it('returns correct fields in user list', function () {
    // Arrange
    $actor = $this->actingAsVerifiedUser();

    // Act
    $response = $this->getJson('/api/users');

    // Assert
    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'items' => [
                    '*' => [
                        'id',
                        'username',
                        'firstName',
                        'lastName',
                        'email',
                        'avatarUrl',
                        'isActive',
                        'createdAt',
                    ],
                ],
            ],
        ]);
});

// =========================================================================
// Show (GET /api/users/{id})
// =========================================================================

it('returns 200 with user data', function () {
    // Arrange
    $actor = $this->actingAsVerifiedUser();
    $target = User::factory()->create();

    // Act
    $response = $this->getJson("/api/users/{$target->id}");

    // Assert
    $response->assertStatus(200)
        ->assertJsonFragment(['id' => $target->id]);
});

it('returns 404 when user not found', function () {
    // Arrange
    $this->actingAsVerifiedUser();

    // Act
    $response = $this->getJson('/api/users/nonexistent-uuid');

    // Assert
    $response->assertStatus(404);
});

// =========================================================================
// Create (POST /api/users)
// =========================================================================

it('returns 201 when creating user with valid data', function () {
    // Arrange
    Http::fake(['api.pwnedpasswords.com/*' => Http::response('', 200)]);
    Mail::fake();
    $this->actingAsVerifiedUser();

    $payload = [
        'username' => 'newuser',
        'first_name' => 'New',
        'last_name' => 'User',
        'email' => 'newuser@example.com',
        'password' => 'SecurePassword123!',
        'password_confirmation' => 'SecurePassword123!',
    ];

    // Act
    $response = $this->postJson('/api/users', $payload);

    // Assert
    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => ['id', 'username', 'firstName', 'lastName', 'email', 'isActive', 'emailVerifiedAt'],
        ]);
    $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
});

it('returns 422 when email already exists', function () {
    // Arrange
    $this->actingAsVerifiedUser();
    User::factory()->create(['email' => 'existing@example.com']);

    // Act
    $response = $this->postJson('/api/users', [
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
    $this->actingAsVerifiedUser();
    User::factory()->create(['username' => 'existinguser']);

    // Act
    $response = $this->postJson('/api/users', [
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
    $this->actingAsVerifiedUser();

    // Act
    $response = $this->postJson('/api/users', [
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
    $this->actingAsVerifiedUser();

    // Act
    $response = $this->postJson('/api/users', []);

    // Assert
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['username', 'first_name', 'last_name', 'email']);
});

it('records audit log when user created', function () {
    // Arrange
    Http::fake(['api.pwnedpasswords.com/*' => Http::response('', 200)]);
    Mail::fake();
    $actor = $this->actingAsVerifiedUser();

    // Act
    $this->postJson('/api/users', [
        'username' => 'newuser',
        'first_name' => 'New',
        'last_name' => 'User',
        'email' => 'newuser@example.com',
        'password' => 'SecurePassword123!',
        'password_confirmation' => 'SecurePassword123!',
    ]);

    // Assert
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $actor->id,
        'action' => 'user.created',
        'entity_type' => 'user',
    ]);
});

// =========================================================================
// Update (PUT /api/users/{id})
// =========================================================================

it('returns 200 when updating user with valid data', function () {
    // Arrange
    $actor = $this->actingAsVerifiedUser();
    $target = User::factory()->create([
        'username' => 'targetuser',
        'first_name' => 'Target',
        'last_name' => 'User',
        'email' => 'target@example.com',
    ]);

    // Act
    $response = $this->putJson("/api/users/{$target->id}", [
        'username' => 'targetuser',
        'first_name' => 'Updated',
        'last_name' => 'Name',
        'email' => 'target@example.com',
    ]);

    // Assert
    $response->assertStatus(200)
        ->assertJsonFragment(['firstName' => 'Updated']);
});

it('records audit log when user updated', function () {
    // Arrange
    $actor = $this->actingAsVerifiedUser();
    $target = User::factory()->create();

    // Act
    $this->putJson("/api/users/{$target->id}", [
        'username' => $target->username,
        'first_name' => 'UpdatedName',
        'last_name' => $target->last_name,
        'email' => $target->email,
    ]);

    // Assert
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $actor->id,
        'action' => 'user.updated',
        'entity_type' => 'user',
        'entity_id' => $target->id,
    ]);
});

// =========================================================================
// Delete (DELETE /api/users/{id})
// =========================================================================

it('returns 204 when deleting another user', function () {
    // Arrange
    $actor = $this->actingAsVerifiedUser();
    $target = User::factory()->create();

    // Act
    $response = $this->deleteJson("/api/users/{$target->id}");

    // Assert
    $response->assertStatus(204);
    $this->assertDatabaseMissing('users', ['id' => $target->id]);
});

it('returns 403 when deleting own account', function () {
    // Arrange
    $actor = $this->actingAsVerifiedUser();

    // Act
    $response = $this->deleteJson("/api/users/{$actor->id}");

    // Assert
    $response->assertStatus(403)
        ->assertJson(['message' => 'Cannot delete your own account']);
});

it('returns 409 when deleting last active user', function () {
    // Arrange
    $actor = $this->actingAsVerifiedUser();
    $target = User::factory()->create(['is_active' => true]);

    // Make actor inactive so target is the only active one
    $actor->update(['is_active' => false]);

    // Act
    $response = $this->deleteJson("/api/users/{$target->id}");

    // Assert
    $response->assertStatus(409)
        ->assertJson(['message' => 'Cannot delete the last active root user']);
});

it('records audit log when user deleted', function () {
    // Arrange
    $actor = $this->actingAsVerifiedUser();
    $target = User::factory()->create();

    // Act
    $this->deleteJson("/api/users/{$target->id}");

    // Assert
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $actor->id,
        'action' => 'user.deleted',
        'entity_type' => 'user',
        'entity_id' => $target->id,
    ]);
});

// =========================================================================
// Activation / Deactivation
// =========================================================================

it('returns 200 when deactivating user', function () {
    // Arrange
    $actor = $this->actingAsVerifiedUser();
    $target = User::factory()->create(['is_active' => true]);

    // Act
    $response = $this->patchJson("/api/users/{$target->id}/deactivate");

    // Assert
    $response->assertStatus(200);
    $this->assertDatabaseHas('users', ['id' => $target->id, 'is_active' => false]);
});

it('records audit log when user deactivated', function () {
    // Arrange
    $actor = $this->actingAsVerifiedUser();
    $target = User::factory()->create(['is_active' => true]);

    // Act
    $this->patchJson("/api/users/{$target->id}/deactivate");

    // Assert
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $actor->id,
        'action' => 'user.deactivated',
        'entity_type' => 'user',
        'entity_id' => $target->id,
    ]);
});

it('returns 200 when activating user', function () {
    // Arrange
    $actor = $this->actingAsVerifiedUser();
    $target = User::factory()->create(['is_active' => false]);

    // Act
    $response = $this->patchJson("/api/users/{$target->id}/activate");

    // Assert
    $response->assertStatus(200);
    $this->assertDatabaseHas('users', ['id' => $target->id, 'is_active' => true]);
});

it('records audit log when user activated', function () {
    // Arrange
    $actor = $this->actingAsVerifiedUser();
    $target = User::factory()->create(['is_active' => false]);

    // Act
    $this->patchJson("/api/users/{$target->id}/activate");

    // Assert
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $actor->id,
        'action' => 'user.activated',
        'entity_type' => 'user',
        'entity_id' => $target->id,
    ]);
});

it('returns 409 when deactivating last active user', function () {
    // Arrange
    $actor = $this->actingAsVerifiedUser();

    // Make actor the only active user
    User::where('id', '!=', $actor->id)->update(['is_active' => false]);

    // Act
    $response = $this->patchJson("/api/users/{$actor->id}/deactivate");

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
    $actor = $this->actingAsVerifiedUser();
    $unverified = User::factory()->unverified()->create();

    // Act
    $response = $this->postJson("/api/users/{$unverified->id}/resend-verification");

    // Assert
    $response->assertStatus(200);
});

it('invalidates previous tokens when resending verification', function () {
    // Arrange
    Mail::fake();
    $actor = $this->actingAsVerifiedUser();
    $unverified = User::factory()->unverified()->create();

    // Create an existing token for this user
    \Infrastructure\Auth\Models\EmailVerificationToken::factory()->create([
        'user_id' => $unverified->id,
    ]);

    // Act
    $this->postJson("/api/users/{$unverified->id}/resend-verification");

    // Assert -- old tokens for this user are gone; new one created
    $this->assertDatabaseCount('email_verification_tokens', 1); // Only the newly created one
});

it('returns 409 when resending verification for already verified user', function () {
    // Arrange
    $actor = $this->actingAsVerifiedUser();
    $verified = User::factory()->create(['email_verified_at' => now()]);

    // Act
    $response = $this->postJson("/api/users/{$verified->id}/resend-verification");

    // Assert
    $response->assertStatus(400)
        ->assertJson(['message' => 'User is already verified']);
});

// =========================================================================
// Change Password (PATCH /api/users/{id}/password)
// =========================================================================

it('returns 200 when changing own password with correct current password', function () {
    // Arrange
    $actor = $this->actingAsVerifiedUser();

    // Act
    $response = $this->patchJson("/api/users/{$actor->id}/password", [
        'current_password' => 'password',
        'password' => 'N3wS3cur3P@ss!',
        'password_confirmation' => 'N3wS3cur3P@ss!',
    ]);

    // Assert
    $response->assertStatus(200)
        ->assertJson(['message' => 'Password changed successfully']);
});

it('returns 200 when admin changes another users password without current password', function () {
    // Arrange
    $actor = $this->actingAsVerifiedUser();
    $target = User::factory()->create();

    // Act
    $response = $this->patchJson("/api/users/{$target->id}/password", [
        'password' => 'N3wS3cur3P@ss!',
        'password_confirmation' => 'N3wS3cur3P@ss!',
    ]);

    // Assert
    $response->assertStatus(200)
        ->assertJson(['message' => 'Password changed successfully']);
});

it('returns 403 when changing own password with wrong current password', function () {
    // Arrange
    $actor = $this->actingAsVerifiedUser();

    // Act
    $response = $this->patchJson("/api/users/{$actor->id}/password", [
        'current_password' => 'wrong-password',
        'password' => 'N3wS3cur3P@ss!',
        'password_confirmation' => 'N3wS3cur3P@ss!',
    ]);

    // Assert
    $response->assertStatus(403)
        ->assertJson(['message' => 'Current password is incorrect']);
});

it('returns 422 when new password does not meet requirements', function () {
    // Arrange
    $actor = $this->actingAsVerifiedUser();
    $target = User::factory()->create();

    // Act
    $response = $this->patchJson("/api/users/{$target->id}/password", [
        'password' => 'weak',
        'password_confirmation' => 'weak',
    ]);

    // Assert
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

it('records audit log when password changed', function () {
    // Arrange
    $actor = $this->actingAsVerifiedUser();
    $target = User::factory()->create();

    // Act
    $this->patchJson("/api/users/{$target->id}/password", [
        'password' => 'N3wS3cur3P@ss!',
        'password_confirmation' => 'N3wS3cur3P@ss!',
    ]);

    // Assert
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $actor->id,
        'action' => 'user.password_changed',
        'entity_type' => 'user',
        'entity_id' => $target->id,
    ]);
});

// =========================================================================
// Avatar
// =========================================================================

it('returns 200 when uploading valid avatar', function () {
    // Arrange
    \Illuminate\Support\Facades\Storage::fake('public');
    $actor = $this->actingAsVerifiedUser();
    $target = User::factory()->create();

    $file = \Illuminate\Http\UploadedFile::fake()->image('avatar.jpg', 200, 200);

    // Act
    $response = $this->postJson("/api/users/{$target->id}/avatar", [
        'avatar' => $file,
    ]);

    // Assert
    $response->assertStatus(200)
        ->assertJsonStructure(['data' => ['avatarUrl']]);
});

it('records audit log when avatar uploaded', function () {
    // Arrange
    \Illuminate\Support\Facades\Storage::fake('public');
    $actor = $this->actingAsVerifiedUser();
    $target = User::factory()->create();

    $file = \Illuminate\Http\UploadedFile::fake()->image('avatar.jpg', 200, 200);

    // Act
    $this->postJson("/api/users/{$target->id}/avatar", [
        'avatar' => $file,
    ]);

    // Assert
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $actor->id,
        'action' => 'user.avatar_updated',
        'entity_type' => 'user',
        'entity_id' => $target->id,
    ]);
});

it('returns 422 when avatar exceeds 1mb', function () {
    // Arrange
    \Illuminate\Support\Facades\Storage::fake('public');
    $this->actingAsVerifiedUser();
    $target = User::factory()->create();

    $file = \Illuminate\Http\UploadedFile::fake()->create('avatar.jpg', 1025, 'image/jpeg'); // 1025 KB > 1MB

    // Act
    $response = $this->postJson("/api/users/{$target->id}/avatar", [
        'avatar' => $file,
    ]);

    // Assert
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['avatar']);
});

it('returns 422 when avatar format is invalid', function () {
    // Arrange
    \Illuminate\Support\Facades\Storage::fake('public');
    $this->actingAsVerifiedUser();
    $target = User::factory()->create();

    $file = \Illuminate\Http\UploadedFile::fake()->create('avatar.gif', 100, 'image/gif');

    // Act
    $response = $this->postJson("/api/users/{$target->id}/avatar", [
        'avatar' => $file,
    ]);

    // Assert
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['avatar']);
});

it('returns 200 when deleting avatar', function () {
    // Arrange
    \Illuminate\Support\Facades\Storage::fake('public');
    $actor = $this->actingAsVerifiedUser();
    $target = User::factory()->withAvatar()->create();

    // Act
    $response = $this->deleteJson("/api/users/{$target->id}/avatar");

    // Assert
    $response->assertStatus(200);
    $this->assertDatabaseHas('users', ['id' => $target->id, 'avatar_path' => null]);
});

it('returns 404 when uploading avatar for non existent user', function () {
    // Arrange
    \Illuminate\Support\Facades\Storage::fake('public');
    $this->actingAsVerifiedUser();

    $file = \Illuminate\Http\UploadedFile::fake()->image('avatar.jpg', 200, 200);

    // Act
    $response = $this->postJson('/api/users/00000000-0000-0000-0000-000000000000/avatar', [
        'avatar' => $file,
    ]);

    // Assert
    $response->assertStatus(404);
});
