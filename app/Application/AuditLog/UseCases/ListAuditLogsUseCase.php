<?php

declare(strict_types=1);

namespace Application\AuditLog\UseCases;

use Application\AuditLog\DTOs\ListAuditLogsRequest;
use Domain\AuditLog\Ports\AuditLogRepositoryInterface;

class ListAuditLogsUseCase
{
    public function __construct(
        private readonly AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    public function execute(ListAuditLogsRequest $request): array
    {
        $filters = [
            'page' => $request->page,
            'per_page' => $request->perPage,
        ];

        if ($request->userId !== null) {
            $filters['user_id'] = $request->userId;
        }

        if ($request->action !== null) {
            $filters['action'] = $request->action;
        }

        if ($request->entityType !== null) {
            $filters['entity_type'] = $request->entityType;
        }

        if ($request->from !== null) {
            $filters['from'] = $request->from;
        }

        if ($request->to !== null) {
            $filters['to'] = $request->to;
        }

        return $this->auditLogRepository->findPaginated($filters);
    }
}
