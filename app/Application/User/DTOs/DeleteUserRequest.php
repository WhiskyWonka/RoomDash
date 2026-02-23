<?php

declare(strict_types=1);

namespace Application\User\DTOs;

final class DeleteUserRequest
{
    public function __construct(
        public readonly string $id,
        public readonly string $actorId,
        public readonly ?string $ipAddress = null,
        public readonly ?string $userAgent = null,
    ) {}
}
