<?php

namespace App\Http\Middleware;

use Closure;
use DateTimeImmutable;
use Domain\AuditLog\Entities\AuditLog;
use Domain\AuditLog\Ports\AuditLogRepositoryInterface;
use Domain\Shared\Ports\UuidGeneratorInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class AuditLogger
{
    private const ACTION_MAP = [
        'store' => 'created',
        'update' => 'updated',
        'destroy' => 'deleted',
        'deactivate' => 'deactivated',
        'activate' => 'activated',
        'uploadAvatar' => 'avatar_updated',
        'deleteAvatar' => 'avatar_deleted',
    ];

    public function __construct(
        private readonly AuditLogRepositoryInterface $auditLogRepository,
        private readonly UuidGeneratorInterface $uuidGenerator,
    ) {}

    public function handle(Request $request, Closure $next, string $entityType = 'unknown', string $tableName = ''): Response
    {
        // Capture user ID early — session may be invalidated by the controller (e.g. logout)
        $request->attributes->set('audit_captured_user_id', $request->session()->get('admin_user_id'));

        if (in_array($request->method(), ['PUT', 'PATCH', 'DELETE']) && $tableName) {
            $id = $request->route('id');

            if ($id) {
                $oldData = DB::table($tableName)->where('id', $id)->first();

                if ($oldData) {
                    $request->attributes->set('old_values', (array) $oldData);
                }
            }
        }

        $request->attributes->set('audit_entity_type', $entityType);

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode < 200 || $statusCode >= 300) {
            return;
        }

        $method = $request->route()?->getActionMethod();
        $entityType = $request->attributes->get('audit_entity_type', 'unknown');
        $actionVerb = self::ACTION_MAP[$method] ?? $method ?? 'unknown_action';
        $action = "{$entityType}.{$actionVerb}";

        $oldValues = $request->attributes->get('old_values');
        $entityId = $request->route('id') ?? null;
        $newValues = null;

        if ($statusCode !== 204) {
            $decodedResponse = json_decode($response->getContent());

            if (isset($decodedResponse->data) && is_object($decodedResponse->data)) {
                $entityId = $decodedResponse->data->id ?? $entityId;
                $newValues = (array) $decodedResponse->data;
            }
        }

        $userId = $request->session()->get('admin_user_id')
            ?? $request->attributes->get('audit_captured_user_id');

        if ($userId === null) {
            return;
        }

        $this->auditLogRepository->create(new AuditLog(
            id: $this->uuidGenerator->generate(),
            userId: $userId,
            action: $action,
            entityType: $entityType,
            entityId: $entityId,
            oldValues: $oldValues,
            newValues: $newValues,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            createdAt: new DateTimeImmutable,
        ));
    }
}
