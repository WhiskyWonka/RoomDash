<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use Application\AuditLog\DTOs\ListAuditLogsRequest;
use Application\AuditLog\UseCases\ListAuditLogsUseCase;
use Domain\AuditLog\Ports\AuditLogRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuditLogController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ListAuditLogsUseCase $listAuditLogsUseCase,
        private readonly AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $result = $this->listAuditLogsUseCase->execute(new ListAuditLogsRequest(
            page: (int) $request->query('page', '1'),
            perPage: (int) $request->query('per_page', '25'),
            userId: $request->query('user_id'),
            action: $request->query('action'),
            entityType: $request->query('entity_type'),
            from: $request->query('from'),
            to: $request->query('to'),
        ));

        $data = [
            'items' => $result['data'],
            'meta' => [
                'current_page' => $result['current_page'],
                'per_page' => $result['per_page'],
                'total' => $result['total'],
            ],
        ];

        return $this->success($data);
    }

    public function show(string $id): JsonResponse
    {
        if (! Str::isUuid($id)) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $auditLog = $this->auditLogRepository->findById($id);

        if (! $auditLog) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return $this->success($auditLog);
    }
}
