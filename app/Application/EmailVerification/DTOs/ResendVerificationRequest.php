<?php

declare(strict_types=1);

namespace Application\EmailVerification\DTOs;

final class ResendVerificationRequest
{
    public function __construct(
        public readonly string $userId,
        public readonly string $actorId,
    ) {}
}
