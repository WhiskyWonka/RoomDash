<?php

declare(strict_types=1);

namespace Infrastructure\Auth\Models;

use Database\Factories\EmailVerificationTokenFactory;
use DateTimeImmutable;
use Domain\Auth\ValueObjects\EmailVerificationToken as EmailVerificationTokenVO;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailVerificationToken extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'email_verification_tokens';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'token',
        'expires_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    protected static function newFactory(): EmailVerificationTokenFactory
    {
        return EmailVerificationTokenFactory::new();
    }

    public function toValueObject(): EmailVerificationTokenVO
    {
        return new EmailVerificationTokenVO(
            id: $this->id,
            userId: $this->user_id,
            hashedToken: $this->token,
            expiresAt: new DateTimeImmutable($this->expires_at->toDateTimeString()),
            createdAt: new DateTimeImmutable($this->created_at->toDateTimeString()),
        );
    }
}
