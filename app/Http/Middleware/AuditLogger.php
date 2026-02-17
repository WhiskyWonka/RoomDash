<?php

namespace App\Http\Middleware;

use Closure;
use DateTimeImmutable;
use Domain\AuditLog\Entities\AuditLog;
use Domain\AuditLog\Ports\AuditLogRepositoryInterface;
use Domain\Shared\Ports\UuidGeneratorInterface;
use Illuminate\Support\Facades\Log;

class AuditLogger
{
    public function __construct(
        private readonly AuditLogRepositoryInterface $auditLogRepository,
        private readonly UuidGeneratorInterface $uuidGenerator,
    ) {}

    public function handle($request, Closure $next)
    {
        if (in_array($request->method(), ['PUT', 'PATCH', 'DELETE'])) {

            $id = $request->route('id');

            if ($id) {
                // Hacemos una consulta rápida solo para auditoría
                // Usamos query builder para mayor velocidad y menos consumo de memoria
                $oldData = \Illuminate\Support\Facades\DB::table('root_users')
                    ->where('id', $id)
                    ->first();

                if ($oldData) {
                    // Guardamos el estado previo en los atributos del request
                    $request->attributes->set('old_values', (array) $oldData);
                }
            }
        }

        return $next($request);
    }

    public function terminate($request, $response)
    {
        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            return;
        }
        // 1. Identificar la acción
        $action = $request->route()?->getActionMethod(); // ej: store, update, destroy

        // 2. Intentar obtener la entidad desde los atributos del request
        $entity = $request->attributes->get('manipulated_entity');

        $response = json_decode($response->getContent());

        // Capture old values for audit log

        // 3. Guardar el log
        // Record audit log
        $this->auditLogRepository->create(new AuditLog(
            id: $this->uuidGenerator->generate(),
            userId: $request->session()->get('admin_user_id') ?? 'system',
            action: $action ?? 'unknown_action',
            entityType: $entity,
            entityId: $response->data->id,
            oldValues: $request->attributes->get('old_values') ?? null,
            newValues: [
                'username' => $response->data->username,
                'first_name' => $response->data->firstName,
                'last_name' => $response->data->lastName,
                'email' => $response->data->email,
            ],
            ipAddress: $request->ipAddress,
            userAgent: $request->userAgent,
            createdAt: new DateTimeImmutable,
        ));
    }
}
