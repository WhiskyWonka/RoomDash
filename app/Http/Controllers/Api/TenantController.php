<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Docs\Endpoints\Api\TenantEndpoints;
use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreRequest;
use App\Http\Requests\Tenant\TenantAdminStoreRequest;
use App\Http\Requests\Tenant\TenantAdminUpdateRequest;
use App\Http\Requests\Tenant\UpdateRequest;
use Application\Tenant\DTOs\CreateAdminDTO;
use Application\Tenant\DTOs\UpdateAdminDTO;
use Application\Tenant\UseCases\CreateAdminUseCase;
use Application\Tenant\UseCases\DeleteAdminUseCase;
use Application\Tenant\UseCases\UpdateAdminUseCase;
use Domain\Tenant\Ports\TenantRepositoryInterface;
use Illuminate\Http\JsonResponse;

class TenantController extends Controller implements TenantEndpoints
{
    use ApiResponse;

    public function __construct(
        private readonly TenantRepositoryInterface $tenants,
        private readonly CreateAdminUseCase $createAdminUseCase,
        private readonly UpdateAdminUseCase $updateAdminUseCase,
        private readonly DeleteAdminUseCase $deleteAdminUseCase
    ) {}

    public function index(): JsonResponse
    {
        $data = [
            'items' => $this->tenants->findAll(),
        ];

        return $this->success($data);
    }

    public function show(string $id): JsonResponse
    {
        $tenant = $this->tenants->findById($id);

        if (! $tenant) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return $this->success($tenant);
    }

    public function store(StoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        $tenant = $this->tenants->create($data['name'], $data['domain']);

        return $this->success($tenant, 'Tenant created');
    }

    public function update(UpdateRequest $request, string $id): JsonResponse
    {
        $data = $request->validated();

        $tenant = $this->tenants->update($id, $data['name'], $data['domain']);

        return $this->success($tenant, 'Tenant updated');
    }

    public function deactivate(string $id): JsonResponse
    {
        $this->tenants->deactivate($id);

        return $this->success(null, 'Tenant deactivated');
    }

    public function activate(string $id): JsonResponse
    {
        $this->tenants->activate($id);

        return $this->success(null, 'Tenant activated');
    }

    public function destroy(string $id): JsonResponse
    {
        $this->tenants->delete($id);

        return $this->success(null, 'Tenant deleted');
    }

    public function getAdmin(string $tenantId): JsonResponse
    {
        $user = $this->tenants->findAdminUser($tenantId);

        if (! $user) {
            return $this->error('No admin user found', 404);
        }

        return $this->success($user);
    }

    public function updateTenantAdmin(TenantAdminUpdateRequest $request, string $tenantId): JsonResponse
    {
        $data = $request->validated();

        try {
            $user = $this->updateAdminUseCase->execute($tenantId, new UpdateAdminDTO(
                email: $data['email'],
                username: $data['username'],
                firstName: $data['first_name'],
                lastName: $data['last_name'],
            ));
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), 400);
        }

        return $this->success($user, 'Admin updated');
    }

    public function deleteAdmin(string $tenantId): JsonResponse
    {
        try {
            $this->deleteAdminUseCase->execute($tenantId);
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), 400);
        }

        return $this->success(null, 'Admin deleted');
    }

    public function createTenantAdmin(TenantAdminStoreRequest $request, $tenantId): JsonResponse
    {
        $data = $request->validated();

        try {
            $user = $this->createAdminUseCase->execute($tenantId, new CreateAdminDTO(
                email: $data['email'],
                username: $data['username'],
                firstName: $data['first_name'],
                lastName: $data['last_name'],
            ));
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), 400);
        }

        return $this->success($user, 'Tenant admin created');
    }
}
