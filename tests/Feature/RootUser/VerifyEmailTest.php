<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Infrastructure\Auth\Models\RootUser;
use Infrastructure\Auth\Models\EmailVerificationToken;

uses(RefreshDatabase::class);

// =========================================================================
// POST /api/auth/verify-email
// =========================================================================

it('returns 200 when verifying email with valid token', function () {
    // Arrange
    $rawToken = Str::random(64);
    $user = RootUser::factory()->unverified()->create();

    EmailVerificationToken::factory()->withRawToken($rawToken)->create([
        'user_id' => $user->id,
    ]);

    // Act
    $response = $this->postJson('/api/auth/verify-email', [
        'token' => $rawToken,
        'password' => 'SecurePassword123!',
        'password_confirmation' => 'SecurePassword123!',
    ]);

    // Assert
    $response->assertStatus(200);
    $this->assertDatabaseHas('root_users', [
        'id' => $user->id,
    ]);
    // Verify email_verified_at is now set
    $user->refresh();
    expect($user->email_verified_at)->not->toBeNull();
});

it('consumes token after successful verification', function () {
    // Arrange
    $rawToken = Str::random(64);
    $user = RootUser::factory()->unverified()->create();

    EmailVerificationToken::factory()->withRawToken($rawToken)->create([
        'user_id' => $user->id,
    ]);

    // Act
    $this->postJson('/api/auth/verify-email', [
        'token' => $rawToken,
        'password' => 'SecurePassword123!',
        'password_confirmation' => 'SecurePassword123!',
    ]);

    // Assert -- token must be deleted after use
    $this->assertDatabaseMissing('email_verification_tokens', [
        'user_id' => $user->id,
    ]);
});

it('returns 400 when verification token is expired', function () {
    // Arrange
    $rawToken = Str::random(64);
    $user = RootUser::factory()->unverified()->create();

    EmailVerificationToken::factory()->expired()->withRawToken($rawToken)->create([
        'user_id' => $user->id,
    ]);

    // Act
    $response = $this->postJson('/api/auth/verify-email', [
        'token' => $rawToken,
        'password' => 'SecurePassword123!',
        'password_confirmation' => 'SecurePassword123!',
    ]);

    // Assert
    $response->assertStatus(400)
        ->assertJson(['message' => 'Verification token has expired']);
});

it('returns 400 when token has already been used', function () {
    // Arrange
    $rawToken = Str::random(64);
    $user = RootUser::factory()->unverified()->create();

    // Create token, then simulate it being used by deleting it
    EmailVerificationToken::factory()->withRawToken($rawToken)->create([
        'user_id' => $user->id,
    ]);

    // First use -- should succeed
    $this->postJson('/api/auth/verify-email', [
        'token' => $rawToken,
        'password' => 'SecurePassword123!',
        'password_confirmation' => 'SecurePassword123!',
    ]);

    // Second use -- token is already consumed
    $response = $this->postJson('/api/auth/verify-email', [
        'token' => $rawToken,
        'password' => 'AnotherPassword123!',
        'password_confirmation' => 'AnotherPassword123!',
    ]);

    // Assert
    $response->assertStatus(400);
});

it('returns 422 when password is too short', function () {
    // Arrange
    $rawToken = Str::random(64);
    $user = RootUser::factory()->unverified()->create();

    EmailVerificationToken::factory()->withRawToken($rawToken)->create([
        'user_id' => $user->id,
    ]);

    // Act
    $response = $this->postJson('/api/auth/verify-email', [
        'token' => $rawToken,
        'password' => 'short',
        'password_confirmation' => 'short',
    ]);

    // Assert
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});
