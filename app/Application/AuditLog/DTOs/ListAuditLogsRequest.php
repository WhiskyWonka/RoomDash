<?php

declare(strict_types=1);

namespace Application\AuditLog\DTOs;

final class ListAuditLogsRequest
{
    public function __construct(
        public readonly int $page = 1,
        public readonly int $perPage = 25,
        public readonly ?string $userId = null,
        public readonly ?string $action = null,
        public readonly ?string $entityType = null,
        public readonly ?string $from = null,
        public readonly ?string $to = null,
    ) {}
}
