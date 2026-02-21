<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Docs\Endpoints\Api\TenantEndpoints;
use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreRequest;
use App\Http\Requests\Tenant\UpdateRequest;
use Domain\Tenant\Ports\TenantRepositoryInterface;
use Illuminate\Http\JsonResponse;

class TenantController extends Controller implements TenantEndpoints
{
    use ApiResponse;

    public function __construct(
        private readonly TenantRepositoryInterface $tenants,
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
}
