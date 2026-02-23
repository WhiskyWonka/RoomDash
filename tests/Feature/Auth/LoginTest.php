<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Infrastructure\Auth\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
        'email_verified_at' => now(),
        'is_active' => true,
        'two_factor_enabled' => false,
    ]);
});

test('user can login with valid credentials', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'user' => ['id', 'email'],
                'twoFactorRequired',
                'requiresSetup',
            ],
        ])
        ->assertJson([
            'data' => ['requiresSetup' => true],
        ]);
});

test('user cannot login with invalid credentials', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Invalid credentials',
        ]);
});

test('login requires email and password', function () {
    $response = $this->postJson('/api/auth/login', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'password']);
});

test('unauthenticated user cannot access me endpoint', function () {
    $response = $this->getJson('/api/auth/me');

    $response->assertStatus(401);
});

test('authenticated user can access me endpoint', function () {
    $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response = $this->getJson('/api/auth/me');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'user' => ['id', 'email', 'twoFactorEnabled'],
                'twoFactorVerified',
                'twoFactorPending',
            ],
        ]);
});

test('user can logout', function () {
    $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response = $this->postJson('/api/auth/logout');

    $response->assertStatus(200)
        ->assertJson(['message' => 'Logged out']);

    $this->getJson('/api/auth/me')->assertStatus(401);
});
