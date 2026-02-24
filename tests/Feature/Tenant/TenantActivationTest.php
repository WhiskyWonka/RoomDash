<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Infrastructure\Tenant\Models\Tenant;
use Tests\Helpers\ActsAsAuthenticatedUser;

uses(RefreshDatabase::class, ActsAsAuthenticatedUser::class);

// =========================================================================
// Authorization
// =========================================================================

it('returns 401 when unauthenticated trying to activate a tenant', function () {
    $tenant = Tenant::factory()->inactive()->createQuietly();

    $this->patchJson("/api/tenants/{$tenant->id}/activate")
        ->assertStatus(401);
});

it('returns 401 when unauthenticated trying to deactivate a tenant', function () {
    $tenant = Tenant::factory()->createQuietly();

    $this->patchJson("/api/tenants/{$tenant->id}/deactivate")
        ->assertStatus(401);
});

it('returns 403 with 2FA_REQUIRED when 2fa not verified on activate', function () {
    $this->actingAsUserPending2FA();
    $tenant = Tenant::factory()->inactive()->createQuietly();

    $this->patchJson("/api/tenants/{$tenant->id}/activate")
        ->assertStatus(403)
        ->assertJson(['errors' => ['code' => '2FA_REQUIRED']]);
});

it('returns 403 with 2FA_REQUIRED when 2fa not verified on deactivate', function () {
    $this->actingAsUserPending2FA();
    $tenant = Tenant::factory()->createQuietly();

    $this->patchJson("/api/tenants/{$tenant->id}/deactivate")
        ->assertStatus(403)
        ->assertJson(['errors' => ['code' => '2FA_REQUIRED']]);
});

// =========================================================================
// Activate (PATCH /api/tenants/{id}/activate)
// =========================================================================

it('returns 200 when activating an inactive tenant', function () {
    $this->actingAsVerifiedUser();
    $tenant = Tenant::factory()->inactive()->createQuietly();

    $this->patchJson("/api/tenants/{$tenant->id}/activate")
        ->assertStatus(200)
        ->assertJson(['message' => 'Tenant activated']);
});

it('sets is_active to true when activating a tenant', function () {
    $this->actingAsVerifiedUser();
    $tenant = Tenant::factory()->inactive()->createQuietly();

    $this->patchJson("/api/tenants/{$tenant->id}/activate");

    $this->assertDatabaseHas('tenants', [
        'id' => $tenant->id,
        'is_active' => true,
    ]);
});

it('returns 404 when activating a non-existent tenant', function () {
    $this->actingAsVerifiedUser();

    $this->patchJson('/api/tenants/00000000-0000-0000-0000-000000000000/activate')
        ->assertNotFound();
});

// =========================================================================
// Deactivate (PATCH /api/tenants/{id}/deactivate)
// =========================================================================

it('returns 200 when deactivating an active tenant', function () {
    $this->actingAsVerifiedUser();
    $tenant = Tenant::factory()->createQuietly();

    $this->patchJson("/api/tenants/{$tenant->id}/deactivate")
        ->assertStatus(200)
        ->assertJson(['message' => 'Tenant deactivated']);
});

it('sets is_active to false when deactivating a tenant', function () {
    $this->actingAsVerifiedUser();
    $tenant = Tenant::factory()->createQuietly();

    $this->patchJson("/api/tenants/{$tenant->id}/deactivate");

    $this->assertDatabaseHas('tenants', [
        'id' => $tenant->id,
        'is_active' => false,
    ]);
});

it('returns 404 when deactivating a non-existent tenant', function () {
    $this->actingAsVerifiedUser();

    $this->patchJson('/api/tenants/00000000-0000-0000-0000-000000000000/deactivate')
        ->assertNotFound();
});
