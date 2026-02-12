<?php

declare(strict_types=1);

namespace Infrastructure\Auth\Adapters;

use Domain\Auth\Entities\RootUser as RootUserEntity;
use Domain\Auth\Ports\RootUserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Infrastructure\Auth\Models\RootUser;

class EloquentRootUserRepository implements RootUserRepositoryInterface
{
    public function findById(string $id): ?RootUserEntity
    {
        $model = RootUser::find($id);

        return $model?->toEntity();
    }

    public function findByEmail(string $email): ?RootUserEntity
    {
        $model = RootUser::where('email', $email)->first();

        return $model?->toEntity();
    }

    public function countActive(): int
    {
        return RootUser::where('is_active', true)->count();
    }

    public function existsByEmail(string $email): bool
    {
        return RootUser::where('email', $email)->exists();
    }

    public function existsByUsername(string $username): bool
    {
        return RootUser::where('username', $username)->exists();
    }

    public function create(array $data): RootUserEntity
    {
        $model = RootUser::create([
            'username' => $data['username'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => null,
            'is_active' => true,
            'email_verified_at' => null,
            'two_factor_enabled' => false,
        ]);

        return $model->toEntity();
    }

    public function update(string $id, array $data): RootUserEntity
    {
        $model = RootUser::findOrFail($id);
        $model->update($data);
        $model->refresh();

        return $model->toEntity();
    }

    public function delete(string $id): void
    {
        RootUser::where('id', $id)->delete();
    }

    public function clearEmailVerification(string $id): void
    {
        RootUser::where('id', $id)->update([
            'email_verified_at' => null,
        ]);
    }

    public function verifyEmail(string $id, string $hashedPassword): void
    {
        RootUser::where('id', $id)->update([
            'email_verified_at' => now(),
            'password' => $hashedPassword,
        ]);
    }

    public function verifyPassword(string $email, string $password): ?RootUserEntity
    {
        $model = RootUser::where('email', $email)->first();

        if (! $model || ! $model->password || ! Hash::check($password, $model->password)) {
            return null;
        }

        return $model->toEntity();
    }

    public function getTwoFactorSecret(string $id): ?string
    {
        $model = RootUser::find($id);

        return $model?->two_factor_secret;
    }

    public function setTwoFactorSecret(string $id, string $secret): void
    {
        $model = RootUser::find($id);
        if ($model) {
            $model->two_factor_secret = $secret;
            $model->save();
        }
    }

    public function enableTwoFactor(string $id): void
    {
        RootUser::where('id', $id)->update([
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now(),
        ]);
    }

    public function disableTwoFactor(string $id): void
    {
        RootUser::where('id', $id)->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);
    }

    public function getRecoveryCodes(string $id): array
    {
        $model = RootUser::find($id);

        return $model?->two_factor_recovery_codes ?? [];
    }

    public function setRecoveryCodes(string $id, array $codes): void
    {
        $model = RootUser::find($id);
        if ($model) {
            $model->two_factor_recovery_codes = $codes;
            $model->save();
        }
    }

    public function useRecoveryCode(string $id, string $code): bool
    {
        $model = RootUser::find($id);
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
}
