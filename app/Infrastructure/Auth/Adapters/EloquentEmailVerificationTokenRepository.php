<?php

declare(strict_types=1);

namespace Infrastructure\Auth\Adapters;

use Domain\Auth\Ports\EmailVerificationTokenRepositoryInterface;
use Domain\Auth\ValueObjects\EmailVerificationToken as EmailVerificationTokenVO;
use Infrastructure\Auth\Models\EmailVerificationToken;

class EloquentEmailVerificationTokenRepository implements EmailVerificationTokenRepositoryInterface
{
    public function findByHashedToken(string $hashedToken): ?EmailVerificationTokenVO
    {
        $model = EmailVerificationToken::where('token', $hashedToken)->first();

        return $model?->toValueObject();
    }

    public function deleteByUserId(string $userId): void
    {
        EmailVerificationToken::where('user_id', $userId)->delete();
    }
}
