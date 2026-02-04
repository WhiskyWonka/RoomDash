<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'RoomDash API',
    description: 'Multi-tenant property management API',
    contact: new OA\Contact(email: 'support@roomdash.example')
)]
#[OA\Server(url: '/api', description: 'API Server')]
#[OA\Schema(
    schema: 'Tenant',
    type: 'object',
    required: ['id', 'name', 'domain', 'createdAt'],
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000'),
        new OA\Property(property: 'name', type: 'string', maxLength: 255, example: 'Acme Corp'),
        new OA\Property(property: 'domain', type: 'string', maxLength: 255, example: 'acme'),
        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time', example: '2025-02-02T22:49:00+00:00'),
    ]
)]
#[OA\Schema(
    schema: 'TenantRequest',
    type: 'object',
    required: ['name', 'domain'],
    properties: [
        new OA\Property(property: 'name', type: 'string', maxLength: 255, example: 'Acme Corp'),
        new OA\Property(property: 'domain', type: 'string', maxLength: 255, example: 'acme'),
    ]
)]
#[OA\Schema(
    schema: 'ErrorResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Not found'),
    ]
)]
#[OA\Schema(
    schema: 'ValidationErrorResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'The name field is required.'),
        new OA\Property(property: 'errors', type: 'object', additionalProperties: new OA\AdditionalProperties(type: 'array', items: new OA\Items(type: 'string'))),
    ]
)]
#[OA\Schema(
    schema: 'HealthResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'status', type: 'string', example: 'ok'),
    ]
)]
abstract class Controller
{
    //
}
