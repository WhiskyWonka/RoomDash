<?php

declare(strict_types=1);

namespace Application\Login\DTOs;

final class CreateLoginReesponse
{
    public function __construct(
        public readonly array $user,
        public readonly bool $twoFactorRequired,
        public readonly bool $requiresSetup,
    ) {}
}
