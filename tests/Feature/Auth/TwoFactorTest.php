<?php

declare(strict_types=1);

use Domain\Auth\Ports\TwoFactorServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Infrastructure\Auth\Models\AdminUser;

uses(RefreshDatabase::class);

beforeEach(function () {
    AdminUser::create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
        'two_factor_enabled' => false,
    ]);
});

test('user can get 2fa setup', function () {
    $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response = $this->getJson('/api/auth/2fa/setup');

    $response->assertStatus(200)
        ->assertJsonStructure(['secret', 'qrCode']);
});

test('user can confirm 2fa setup with valid code', function () {
    $google2fa = new \PragmaRX\Google2FA\Google2FA();
    $secret = $google2fa->generateSecretKey();

    // Pre-set the secret so we know what it is
    $user = AdminUser::first();
    $user->two_factor_secret = $secret;
    $user->save();

    $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $code = $google2fa->getCurrentOtp($secret);

    $response = $this->postJson('/api/auth/2fa/confirm', [
        'code' => $code,
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['recoveryCodes', 'enabled'])
        ->assertJson(['enabled' => true]);

    expect($response->json('recoveryCodes'))->toHaveCount(8);
});

test('user cannot confirm 2fa with invalid code', function () {
    $google2fa = new \PragmaRX\Google2FA\Google2FA();
    $secret = $google2fa->generateSecretKey();

    $user = AdminUser::first();
    $user->two_factor_secret = $secret;
    $user->save();

    $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response = $this->postJson('/api/auth/2fa/confirm', [
        'code' => '000000',
    ]);

    $response->assertStatus(400)
        ->assertJson(['message' => 'Invalid code']);
});

test('user with 2fa can verify code', function () {
    $google2fa = new \PragmaRX\Google2FA\Google2FA();
    $secret = $google2fa->generateSecretKey();

    $user = AdminUser::first();
    $user->two_factor_secret = $secret;
    $user->two_factor_enabled = true;
    $user->two_factor_confirmed_at = now();
    $user->save();

    // Refresh user to get encrypted secret
    $user->refresh();

    $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    // Use the original unencrypted secret for OTP generation
    $code = $google2fa->getCurrentOtp($secret);

    $response = $this->postJson('/api/auth/verify-2fa', [
        'code' => $code,
    ]);

    $response->assertStatus(200)
        ->assertJson(['verified' => true]);
});

test('tenants api requires 2fa verification', function () {
    $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response = $this->getJson('/api/tenants');

    $response->assertStatus(403)
        ->assertJson(['code' => '2FA_REQUIRED']);
});
