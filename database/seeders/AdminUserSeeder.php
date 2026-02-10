<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the admin user.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@roomdash.local');
        $password = env('ADMIN_PASSWORD', 'password');

        DB::table('admin_users')->updateOrInsert(
            ['email' => $email],
            [
                'id' => Str::uuid()->toString(),
                'email' => $email,
                'password' => Hash::make($password),
                'two_factor_secret' => null,
                'two_factor_enabled' => false,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->command->info("Admin user created: {$email}");
    }
}
