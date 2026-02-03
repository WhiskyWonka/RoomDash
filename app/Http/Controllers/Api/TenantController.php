<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Domain\Tenant\Ports\TenantRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function __construct(
        private readonly TenantRepositoryInterface $tenants,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json($this->tenants->findAll());
    }

    public function show(string $id): JsonResponse
    {
        $tenant = $this->tenants->findById($id);

        if (! $tenant) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json($tenant);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255',
        ]);

        $tenant = $this->tenants->create($data['name'], $data['domain']);

        return response()->json($tenant, 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255',
        ]);

        $tenant = $this->tenants->update($id, $data['name'], $data['domain']);

        return response()->json($tenant);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->tenants->delete($id);

        return response()->json(null, 204);
    }
}
