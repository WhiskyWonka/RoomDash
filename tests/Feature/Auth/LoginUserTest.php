<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Infrastructure\Auth\Models\User;
use Tests\Helpers\ActsAsAuthenticatedUser;

uses(RefreshDatabase::class, ActsAsAuthenticatedUser::class);

// =========================================================================
// Login flow - unverified and deactivated checks (BR-008, BR-009)
// =========================================================================

it('returns 401 with success false when user is not verified', function () {
    // Arrange
    User::factory()->create([
        'email' => 'unverified@example.com',
        'password' => Hash::make('password'),
        'email_verified_at' => null,
        'is_active' => true,
    ]);

    // Act
    $response = $this->postJson('/api/auth/login', [
        'email' => 'unverified@example.com',
        'password' => 'password',
    ]);

    // Assert
    $response->assertStatus(401)
        ->assertJson(['success' => false]);
});

it('returns 401 with success false when user is deactivated', function () {
    // Arrange
    User::factory()->create([
        'email' => 'deactivated@example.com',
        'password' => Hash::make('password'),
        'email_verified_at' => now(),
        'is_active' => false,
    ]);

    // Act
    $response = $this->postJson('/api/auth/login', [
        'email' => 'deactivated@example.com',
        'password' => 'password',
    ]);

    // Assert
    $response->assertStatus(401)
        ->assertJson(['success' => false]);
});

// =========================================================================
// Audit logging on login/logout
// =========================================================================

it('records audit log on successful login after 2fa', function () {
    // Arrange
    $google2fa = new \PragmaRX\Google2FA\Google2FA;
    $secret = $google2fa->generateSecretKey();

    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
        'email_verified_at' => now(),
        'is_active' => true,
        'two_factor_enabled' => true,
        'two_factor_confirmed_at' => now(),
    ]);

    $user->two_factor_secret = $secret;
    $user->save();

    // Login step
    $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    // 2FA step
    $code = $google2fa->getCurrentOtp($secret);
    $this->postJson('/api/auth/verify-2fa', ['code' => $code]);

    // Assert
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $user->id,
        'action' => 'user.verify2fa',
    ]);
});

it('records audit log on logout', function () {
    // Arrange
    $actor = $this->actingAsVerifiedUser();

    // Act
    $this->postJson('/api/auth/logout');

    // Assert
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $actor->id,
        'action' => 'auth.logout',
    ]);
});
