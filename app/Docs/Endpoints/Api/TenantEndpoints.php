<?php

namespace App\Docs\Endpoints\Api;

use App\Http\Requests\Tenant\StoreRequest;
use App\Http\Requests\Tenant\UpdateRequest;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

interface TenantEndpoints
{
    #[OA\Get(
        path: '/tenants',
        summary: 'List all tenants',
        description: 'Returns a list of all tenants in the system',
        operationId: 'getTenants',
        tags: ['Tenants'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/Tenant')
                )
            ),
        ]
    )]
    public function index(): JsonResponse;

    #[OA\Get(
        path: '/tenants/{id}',
        summary: 'Get a tenant by ID',
        description: 'Returns a single tenant',
        operationId: 'getTenantById',
        tags: ['Tenants'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'Tenant UUID',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(ref: '#/components/schemas/Tenant')
            ),
            new OA\Response(
                response: 404,
                description: 'Tenant not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function show(string $id): JsonResponse;

    #[OA\Post(
        path: '/tenants',
        summary: 'Create a new tenant',
        description: 'Creates a new tenant with the provided data',
        operationId: 'createTenant',
        tags: ['Tenants'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/TenantRequest')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Tenant created successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/Tenant')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')
            ),
        ]
    )]
    public function store(StoreRequest $request): JsonResponse;

    #[OA\Put(
        path: '/tenants/{id}',
        summary: 'Update a tenant',
        description: 'Updates an existing tenant with the provided data',
        operationId: 'updateTenant',
        tags: ['Tenants'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'Tenant UUID',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/TenantRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Tenant updated successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/Tenant')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')
            ),
        ]
    )]
    public function update(UpdateRequest $request, string $id): JsonResponse;

    #[OA\Delete(
        path: '/tenants/{id}',
        summary: 'Delete a tenant',
        description: 'Deletes a tenant and all associated data',
        operationId: 'deleteTenant',
        tags: ['Tenants'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'Tenant UUID',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Tenant deleted successfully'
            ),
        ]
    )]
    public function destroy(string $id): JsonResponse;
}
