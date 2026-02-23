<?php

declare(strict_types=1);

namespace Application\User\DTOs;

final class ChangePasswordRequest
{
    public function __construct(
        public readonly string $id,
        public readonly string $newPassword,
        public readonly ?string $currentPassword,
        public readonly string $actorId,
        public readonly ?string $ipAddress = null,
        public readonly ?string $userAgent = null,
    ) {}
}
