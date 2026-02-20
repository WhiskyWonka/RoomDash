<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Infrastructure\Auth\Models\RootUser;
use Tests\Helpers\ActsAsAuthenticatedRootUser;

uses(RefreshDatabase::class, ActsAsAuthenticatedRootUser::class);

// =========================================================================
// Login flow - unverified and deactivated checks (BR-008, BR-009)
// =========================================================================

it('returns 403 with EMAIL_NOT_VERIFIED when user is not verified', function () {
    // Arrange
    RootUser::factory()->create([
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
    $response->assertStatus(403)
        ->assertJson(['errors' => ['code' => 'EMAIL_NOT_VERIFIED']]);
});

it('returns 403 with ACCOUNT_DEACTIVATED when user is deactivated', function () {
    // Arrange
    RootUser::factory()->create([
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
    $response->assertStatus(403)
        ->assertJson(['errors' => ['code' => 'ACCOUNT_DEACTIVATED']]);
});

// =========================================================================
// Audit logging on login/logout
// =========================================================================

it('records audit log on successful login after 2fa', function () {
    // Arrange
    $google2fa = new \PragmaRX\Google2FA\Google2FA;
    $secret = $google2fa->generateSecretKey();

    $user = RootUser::factory()->create([
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
        'action' => 'root_user.verify2fa',
    ]);
});

it('records audit log on logout', function () {
    // Arrange
    $actor = $this->actingAsVerifiedRootUser();

    // Act
    $this->postJson('/api/auth/logout');

    // Assert
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $actor->id,
        'action' => 'auth.logout',
    ]);
});
