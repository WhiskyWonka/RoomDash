<?php

declare(strict_types=1);

namespace Application\EmailVerification\DTOs;

final class VerifyEmailDTO
{
    public function __construct(
        public readonly string $token,
        public readonly string $password,
        public readonly string $passwordConfirmation,
    ) {}
}
