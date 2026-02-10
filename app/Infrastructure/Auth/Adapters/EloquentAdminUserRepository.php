<?php

declare(strict_types=1);

namespace Infrastructure\Auth\Adapters;

use Domain\Auth\Entities\AdminUser as AdminUserEntity;
use Domain\Auth\Ports\AdminUserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Infrastructure\Auth\Models\AdminUser;

class EloquentAdminUserRepository implements AdminUserRepositoryInterface
{
    public function findById(string $id): ?AdminUserEntity
    {
        $model = AdminUser::find($id);

        return $model?->toEntity();
    }

    public function findByEmail(string $email): ?AdminUserEntity
    {
        $model = AdminUser::where('email', $email)->first();

        return $model?->toEntity();
    }

    public function verifyPassword(string $email, string $password): ?AdminUserEntity
    {
        $model = AdminUser::where('email', $email)->first();

        if (! $model || ! Hash::check($password, $model->password)) {
            return null;
        }

        return $model->toEntity();
    }

    public function getTwoFactorSecret(string $id): ?string
    {
        $model = AdminUser::find($id);

        return $model?->two_factor_secret;
    }

    public function setTwoFactorSecret(string $id, string $secret): void
    {
        $model = AdminUser::find($id);
        if ($model) {
            $model->two_factor_secret = $secret;
            $model->save();
        }
    }

    public function enableTwoFactor(string $id): void
    {
        AdminUser::where('id', $id)->update([
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now(),
        ]);
    }

    public function disableTwoFactor(string $id): void
    {
        AdminUser::where('id', $id)->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);
    }

    public function getRecoveryCodes(string $id): array
    {
        $model = AdminUser::find($id);

        return $model?->two_factor_recovery_codes ?? [];
    }

    public function setRecoveryCodes(string $id, array $codes): void
    {
        $model = AdminUser::find($id);
        if ($model) {
            $model->two_factor_recovery_codes = $codes;
            $model->save();
        }
    }

    public function useRecoveryCode(string $id, string $code): bool
    {
        $model = AdminUser::find($id);
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
