<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Infrastructure\Auth\Models\EmailVerificationToken>
 */
class EmailVerificationTokenFactory extends Factory
{
    protected $model = \Infrastructure\Auth\Models\EmailVerificationToken::class;

    public function definition(): array
    {
        $rawToken = Str::random(64);

        return [
            'user_id' => \Infrastructure\Auth\Models\User::factory(),
            'token' => hash('sha256', $rawToken),
            'expires_at' => now()->addHours(24),
            'created_at' => now(),
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subSeconds(1),
        ]);
    }

    public function withRawToken(string $rawToken): static
    {
        return $this->state(fn (array $attributes) => [
            'token' => hash('sha256', $rawToken),
        ]);
    }
}
