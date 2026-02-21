<?php

declare(strict_types=1);

namespace Infrastructure\Auth\Adapters;

use Domain\Auth\Entities\User as UserEntity;
use Domain\Auth\Ports\UserRepositoryInterface;
use Domain\Shared\DTOs\PaginatedResult;
use Illuminate\Support\Facades\Hash;
use Infrastructure\Auth\Models\User;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function findById(string $id): ?UserEntity
    {
        $model = User::find($id);

        return $model?->toEntity();
    }

    public function findByEmail(string $email): ?UserEntity
    {
        $model = User::where('email', $email)->first();

        return $model?->toEntity();
    }

    public function countActive(): int
    {
        return User::where('is_active', true)->count();
    }

    public function existsById(string $id): bool
    {
        return User::where('id', $id)->exists();
    }

    public function existsByEmail(string $email): bool
    {
        return User::where('email', $email)->exists();
    }

    public function existsByUsername(string $username): bool
    {
        return User::where('username', $username)->exists();
    }

    public function create(array $data): UserEntity
    {
        $model = User::create([
            'username' => $data['username'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'is_active' => true,
            'email_verified_at' => null,
            'two_factor_enabled' => false,
        ]);

        return $model->toEntity();
    }

    public function update(string $id, array $data): UserEntity
    {
        $model = User::findOrFail($id);
        $model->update($data);
        $model->refresh();

        return $model->toEntity();
    }

    public function delete(string $id): void
    {
        User::where('id', $id)->delete();
    }

    public function clearEmailVerification(string $id): void
    {
        User::where('id', $id)->update([
            'email_verified_at' => null,
        ]);
    }

    public function verifyEmail(string $id, string $hashedPassword): void
    {
        User::where('id', $id)->update([
            'email_verified_at' => now(),
            'password' => $hashedPassword,
        ]);
    }

    public function verifyPassword(string $email, string $password): ?UserEntity
    {
        $model = User::where('email', $email)->first();

        if (! $model || ! $model->password || ! Hash::check($password, $model->password)) {
            return null;
        }

        return $model->toEntity();
    }

    public function getTwoFactorSecret(string $id): ?string
    {
        $model = User::find($id);

        return $model?->two_factor_secret;
    }

    public function setTwoFactorSecret(string $id, string $secret): void
    {
        $model = User::find($id);
        if ($model) {
            $model->two_factor_secret = $secret;
            $model->save();
        }
    }

    public function enableTwoFactor(string $id): void
    {
        User::where('id', $id)->update([
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now(),
        ]);
    }

    public function disableTwoFactor(string $id): void
    {
        User::where('id', $id)->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);
    }

    public function getRecoveryCodes(string $id): array
    {
        $model = User::find($id);

        return $model?->two_factor_recovery_codes ?? [];
    }

    public function setRecoveryCodes(string $id, array $codes): void
    {
        $model = User::find($id);
        if ($model) {
            $model->two_factor_recovery_codes = $codes;
            $model->save();
        }
    }

    public function useRecoveryCode(string $id, string $code): bool
    {
        $model = User::find($id);
        if (! $model) {
            return false;
        }

        $codes = $model->two_factor_recovery_codes ?? [];
        $hashedCode = hash('sha256', $code);
        $index = array_search($hashedCode, $codes, true);

        if ($index === false) {
            return false;
        }

        unset($codes[$index]);
        $model->two_factor_recovery_codes = array_values($codes);
        $model->save();

        return true;
    }

    public function listPaginated(
        int $page,
        int $perPage,
        string $sortField,
        string $sortDirection
    ): PaginatedResult {

        // Whitelist de columnas permitidas para evitar errores
        $allowedFields = ['username', 'email', 'created_at', 'is_active'];
        $field = in_array($sortField, $allowedFields) ? $sortField : 'created_at';

        // Validar direccion
        $direction = strtolower($sortDirection) === 'asc' ? 'asc' : 'desc';

        $resultModels = User::orderBy($field, $direction)
            ->paginate($perPage, ['*'], 'page', $page);

        $result = $resultModels->map(fn (User $model) => $model->toEntity());

        return new PaginatedResult($result->toArray(), $resultModels->total(), $perPage, $page);
    }
}
