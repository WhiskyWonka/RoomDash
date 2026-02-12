<?php

declare(strict_types=1);

namespace Infrastructure\AuditLog\Adapters;

use Domain\AuditLog\Entities\AuditLog as AuditLogEntity;
use Domain\AuditLog\Ports\AuditLogRepositoryInterface;
use Infrastructure\AuditLog\Models\AuditLog;

class EloquentAuditLogRepository implements AuditLogRepositoryInterface
{
    public function create(AuditLogEntity $auditLog): void
    {
        AuditLog::create([
            'id' => $auditLog->id,
            'user_id' => $auditLog->userId,
            'action' => $auditLog->action,
            'entity_type' => $auditLog->entityType,
            'entity_id' => $auditLog->entityId,
            'old_values' => $auditLog->oldValues,
            'new_values' => $auditLog->newValues,
            'ip_address' => $auditLog->ipAddress,
            'user_agent' => $auditLog->userAgent,
            'created_at' => $auditLog->createdAt->format('Y-m-d H:i:s'),
        ]);
    }

    public function findById(string $id): ?AuditLogEntity
    {
        $model = AuditLog::find($id);

        return $model?->toEntity();
    }

    public function findPaginated(array $filters): array
    {
        $query = AuditLog::query()->orderBy('created_at', 'desc');

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (isset($filters['entity_type'])) {
            $query->where('entity_type', $filters['entity_type']);
        }

        if (isset($filters['from'])) {
            $query->where('created_at', '>=', $filters['from'] . ' 00:00:00');
        }

        if (isset($filters['to'])) {
            $query->where('created_at', '<=', $filters['to'] . ' 23:59:59');
        }

        $perPage = $filters['per_page'] ?? 25;
        $page = $filters['page'] ?? 1;

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => collect($paginator->items())->map(fn (AuditLog $model) => [
                'id' => $model->id,
                'userId' => $model->user_id,
                'action' => $model->action,
                'entityType' => $model->entity_type,
                'entityId' => $model->entity_id,
                'oldValues' => $model->old_values,
                'newValues' => $model->new_values,
                'ipAddress' => $model->ip_address,
                'userAgent' => $model->user_agent,
                'createdAt' => $model->created_at?->toIso8601String(),
            ])->all(),
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
        ];
    }
}
