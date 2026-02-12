<?php

declare(strict_types=1);

namespace Tests\Helpers;

use Illuminate\Support\Facades\Hash;
use Infrastructure\Auth\Models\RootUser;

trait ActsAsAuthenticatedRootUser
{
    /**
     * Create a fully authenticated root user with 2FA verified session.
     */
    protected function actingAsVerifiedRootUser(?RootUser $user = null): RootUser
    {
        $user = $user ?? RootUser::factory()->create([
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
     * Create a root user authenticated but 2FA not yet verified.
     */
    protected function actingAsRootUserPending2FA(?RootUser $user = null): RootUser
    {
        $user = $user ?? RootUser::factory()->create([
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $this->withSession([
            'admin_user_id' => $user->id,
            '2fa_pending' => true,
            '2fa_verified' => false,
        ]);

        return $user;
    }
}
