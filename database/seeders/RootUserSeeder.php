<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RootUserSeeder extends Seeder
{
     public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@roomdash.local');
        $password = env('ADMIN_PASSWORD', 'password');
        $existingUser = DB::table('root_users')->where('email', $email)->first();

        DB::table('root_users')->updateOrInsert(
            ['email' => $email],
            [
                'id' => $existingUser->id ?? Str::uuid()->toString(),
                'username' => 'admin',
                'first_name' => 'System',
                'last_name' => 'Administrator',
                'email' => $email,
                'password' => Hash::make($password),
                'two_factor_secret' => null,
                'two_factor_enabled' => false,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'is_active' => true,
                'email_verified_at' => now(),
                'avatar_path' => null,
                'remember_token' => null
            ]
        );

        $this->command->info("Root user created: {$email}");
    }
}