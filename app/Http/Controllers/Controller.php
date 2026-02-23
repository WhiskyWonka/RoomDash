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
#[OA\Schema(
    schema: 'AdminUser',
    type: 'object',
    required: ['id', 'email', 'twoFactorEnabled', 'createdAt'],
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'admin@roomdash.local'),
        new OA\Property(property: 'twoFactorEnabled', type: 'boolean', example: true),
        new OA\Property(property: 'twoFactorConfirmedAt', type: 'string', format: 'date-time', nullable: true, example: '2025-02-02T22:49:00+00:00'),
        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time', example: '2025-02-02T22:49:00+00:00'),
    ]
)]
#[OA\Schema(
    schema: 'LoginRequest',
    type: 'object',
    required: ['email', 'password'],
    properties: [
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'admin@roomdash.local'),
        new OA\Property(property: 'password', type: 'string', example: 'password'),
    ]
)]
#[OA\Schema(
    schema: 'LoginResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'user', ref: '#/components/schemas/AdminUser'),
        new OA\Property(property: 'twoFactorEnabled', type: 'boolean', example: true),
        new OA\Property(property: 'requiresTwoFactorSetup', type: 'boolean', example: false),
    ]
)]
#[OA\Schema(
    schema: 'Verify2faRequest',
    type: 'object',
    required: ['code'],
    properties: [
        new OA\Property(property: 'code', type: 'string', minLength: 6, maxLength: 6, example: '123456'),
    ]
)]
#[OA\Schema(
    schema: 'Verify2faResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'user', ref: '#/components/schemas/AdminUser'),
        new OA\Property(property: 'verified', type: 'boolean', example: true),
    ]
)]
#[OA\Schema(
    schema: 'TwoFactorSetupResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'secret', type: 'string', example: 'JBSWY3DPEHPK3PXP'),
        new OA\Property(property: 'qrCode', type: 'string', format: 'uri', example: 'data:image/svg+xml;base64,...'),
    ]
)]
#[OA\Schema(
    schema: 'TwoFactorConfirmResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'recoveryCodes', type: 'array', items: new OA\Items(type: 'string', example: 'ABCD-EFGH')),
        new OA\Property(property: 'enabled', type: 'boolean', example: true),
    ]
)]
#[OA\Schema(
    schema: 'TwoFactorStatusResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'enabled', type: 'boolean', example: true),
        new OA\Property(property: 'confirmedAt', type: 'string', format: 'date-time', nullable: true, example: '2025-02-02T22:49:00+00:00'),
    ]
)]
#[OA\Schema(
    schema: 'MeResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'user', ref: '#/components/schemas/AdminUser'),
        new OA\Property(property: 'twoFactorVerified', type: 'boolean', example: true),
        new OA\Property(property: 'twoFactorPending', type: 'boolean', example: false),
    ]
)]
#[OA\Schema(
    schema: 'UserSummary',
    type: 'object',
    required: ['id', 'username', 'firstName', 'lastName', 'email', 'isActive', 'createdAt'],
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000'),
        new OA\Property(property: 'username', type: 'string', example: 'johndoe'),
        new OA\Property(property: 'firstName', type: 'string', example: 'John'),
        new OA\Property(property: 'lastName', type: 'string', example: 'Doe'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
        new OA\Property(property: 'avatarUrl', type: 'string', format: 'uri', nullable: true, example: '/storage/avatars/abc123.jpg'),
        new OA\Property(property: 'isActive', type: 'boolean', example: true),
        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time', example: '2025-02-02 22:49:00'),
    ]
)]
#[OA\Schema(
    schema: 'UserDetail',
    type: 'object',
    required: ['id', 'username', 'firstName', 'lastName', 'email', 'isActive', 'twoFactorEnabled', 'createdAt'],
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000'),
        new OA\Property(property: 'username', type: 'string', example: 'johndoe'),
        new OA\Property(property: 'firstName', type: 'string', example: 'John'),
        new OA\Property(property: 'lastName', type: 'string', example: 'Doe'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
        new OA\Property(property: 'avatarUrl', type: 'string', format: 'uri', nullable: true, example: '/storage/avatars/abc123.jpg'),
        new OA\Property(property: 'isActive', type: 'boolean', example: true),
        new OA\Property(property: 'emailVerifiedAt', type: 'string', format: 'date-time', nullable: true, example: '2025-02-02 22:49:00'),
        new OA\Property(property: 'twoFactorEnabled', type: 'boolean', example: false),
        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time', example: '2025-02-02 22:49:00'),
    ]
)]
abstract class Controller
{
    //
}
