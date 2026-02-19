<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('admin_users', 'root_users');

        Schema::table('root_users', function (Blueprint $table) {
            $table->string('username', 50)->unique()->after('id');
            $table->string('first_name')->after('username');
            $table->string('last_name')->after('first_name');
            $table->string('avatar_path', 500)->nullable()->after('password');
            $table->boolean('is_active')->default(true)->after('avatar_path');
            $table->timestamp('email_verified_at')->nullable()->after('is_active');

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('root_users', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropColumn(['username', 'first_name', 'last_name', 'avatar_path', 'is_active', 'email_verified_at']);
            $table->string('password')->nullable(false)->change();
        });

        Schema::rename('root_users', 'admin_users');
    }
};
