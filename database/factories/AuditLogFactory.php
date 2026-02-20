<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Infrastructure\AuditLog\Models\AuditLog>
 */
class AuditLogFactory extends Factory
{
    protected $model = \Infrastructure\AuditLog\Models\AuditLog::class;

    public function definition(): array
    {
        $actions = [
            'root_user.created',
            'root_user.updated',
            'root_user.deleted',
            'root_user.deactivated',
            'root_user.activated',
            'root_user.avatar_updated',
            'auth.login',
            'auth.logout',
            'tenant.created',
            'tenant.updated',
            'tenant.deleted',
        ];

        return [
            'user_id' => $this->faker->uuid(),
            'action' => $this->faker->randomElement($actions),
            'entity_type' => $this->faker->randomElement(['root_user', 'tenant', null]),
            'entity_id' => $this->faker->optional()->uuid(),
            'old_values' => null,
            'new_values' => null,
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'created_at' => now(),
        ];
    }

    public function forAction(string $action): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => $action,
        ]);
    }

    public function forEntityType(string $entityType): static
    {
        return $this->state(fn (array $attributes) => [
            'entity_type' => $entityType,
        ]);
    }

    public function forUser(string $userId): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $userId,
        ]);
    }
}
