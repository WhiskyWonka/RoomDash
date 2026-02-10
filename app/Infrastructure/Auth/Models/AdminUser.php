<?php

declare(strict_types=1);

namespace Infrastructure\Auth\Models;

use DateTimeImmutable;
use Domain\Auth\Entities\AdminUser as AdminUserEntity;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class AdminUser extends Model implements Authenticatable
{
    use HasUuids;

    protected $table = 'admin_users';

    protected $fillable = [
        'email',
        'password',
        'two_factor_secret',
        'two_factor_enabled',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'two_factor_enabled' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getTwoFactorSecretAttribute(?string $value): ?string
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    public function setTwoFactorSecretAttribute(?string $value): void
    {
        $this->attributes['two_factor_secret'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getTwoFactorRecoveryCodesAttribute(?string $value): ?array
    {
        return $value ? json_decode(Crypt::decryptString($value), true) : null;
    }

    public function setTwoFactorRecoveryCodesAttribute(?array $value): void
    {
        $this->attributes['two_factor_recovery_codes'] = $value
            ? Crypt::encryptString(json_encode($value))
            : null;
    }

    public function toEntity(): AdminUserEntity
    {
        return new AdminUserEntity(
            id: $this->id,
            email: $this->email,
            twoFactorEnabled: $this->two_factor_enabled,
            twoFactorConfirmedAt: $this->two_factor_confirmed_at
                ? new DateTimeImmutable($this->two_factor_confirmed_at->toDateTimeString())
                : null,
            createdAt: new DateTimeImmutable($this->created_at->toDateTimeString()),
        );
    }

    // Authenticatable interface methods

    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function getAuthIdentifier(): mixed
    {
        return $this->id;
    }

    public function getAuthPassword(): string
    {
        return $this->password;
    }

    public function getAuthPasswordName(): string
    {
        return 'password';
    }

    public function getRememberToken(): ?string
    {
        return $this->remember_token;
    }

    public function setRememberToken($value): void
    {
        $this->remember_token = $value;
    }

    public function getRememberTokenName(): string
    {
        return 'remember_token';
    }
}
