<?php

declare(strict_types=1);

namespace Domain\AuditLog\Ports;

use Domain\AuditLog\Entities\AuditLog;

interface AuditLogRepositoryInterface
{
    public function create(AuditLog $auditLog): void;

    public function findById(string $id): ?AuditLog;

    public function findPaginated(array $filters): array;
}
