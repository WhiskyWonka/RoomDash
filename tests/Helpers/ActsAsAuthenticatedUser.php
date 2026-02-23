<?php

declare(strict_types=1);

namespace Tests\Helpers;

use Illuminate\Support\Facades\Hash;
use Infrastructure\Auth\Models\User;

trait ActsAsAuthenticatedUser
{
    /**
     * Create a fully authenticated user with 2FA verified session.
     */
    protected function actingAsVerifiedUser(?User $user = null): User
    {
        $user = $user ?? User::factory()->create([
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $this->withSession([
            'admin_user_id' => $user->id,
            '2fa_verified' => true,
        ]);

        return $user;
    }

    /**
     * Create a user authenticated but 2FA not yet verified.
     */
    protected function actingAsUserPending2FA(?User $user = null): User
    {
        $user = $user ?? User::factory()->create([
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $this->actingAs($user, 'admin')->withSession([
            'admin_user_id' => $user->id,
            '2fa_pending' => true,
            '2fa_verified' => false,
        ]);

        return $user;
    }
}
