<?php

declare(strict_types=1);

namespace Infrastructure\Auth\Models;

use Database\Factories\RootUserFactory;
use DateTimeImmutable;
use Domain\Auth\Entities\RootUser as RootUserEntity;
use Domain\Auth\ValueObjects\Username;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class RootUser extends Model implements Authenticatable
{
    use HasFactory;
    use HasUuids;

    protected $table = 'root_users';

    protected $fillable = [
        'username',
        'first_name',
        'last_name',
        'email',
        'password',
        'avatar_path',
        'is_active',
        'email_verified_at',
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
            'is_active' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'email_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function newFactory(): RootUserFactory
    {
        return RootUserFactory::new();
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

    public function toEntity(): RootUserEntity
    {
        return new RootUserEntity(
            id: $this->id,
            username: new Username($this->username),
            firstName: $this->first_name,
            lastName: $this->last_name,
            email: $this->email,
            password: $this->password,
            avatarPath: $this->avatar_path,
            isActive: $this->is_active,
            twoFactorEnabled: $this->two_factor_enabled,
            emailVerifiedAt: $this->email_verified_at
                ? new DateTimeImmutable($this->email_verified_at->toDateTimeString())
                : null,
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
        return $this->password ?? '';
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
