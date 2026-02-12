<?php

declare(strict_types=1);

namespace Application\AuditLog\UseCases;

use Application\AuditLog\DTOs\RecordAuditLogRequest;
use DateTimeImmutable;
use Domain\AuditLog\Entities\AuditLog;
use Domain\AuditLog\Ports\AuditLogRepositoryInterface;
use Illuminate\Support\Str;

class RecordAuditLogUseCase
{
    public function __construct(
        private readonly AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    public function execute(RecordAuditLogRequest $request): void
    {
        $auditLog = new AuditLog(
            id: Str::uuid()->toString(),
            userId: $request->actorId,
            action: $request->action,
            entityType: $request->entityType,
            entityId: $request->entityId,
            oldValues: $request->oldValues,
            newValues: $request->newValues,
            ipAddress: $request->ipAddress,
            userAgent: $request->userAgent,
            createdAt: new DateTimeImmutable(),
        );

        $this->auditLogRepository->create($auditLog);
    }
}
