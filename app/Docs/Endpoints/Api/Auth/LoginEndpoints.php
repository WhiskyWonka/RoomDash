<?php

namespace App\Docs\Endpoints\Api\Auth;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\Verify2FARequest;
use App\Http\Requests\Auth\VerifyRecovery2FARequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

interface LoginEndpoints
{
    #[OA\Post(
        path: '/auth/login',
        summary: 'Login with email and password',
        description: 'Authenticates a root user and initiates 2FA flow',
        operationId: 'login',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/LoginRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login successful',
                content: new OA\JsonContent(ref: '#/components/schemas/LoginResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid credentials',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 429,
                description: 'Too many login attempts',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function login(LoginRequest $request): JsonResponse;

    #[OA\Post(
        path: '/auth/verify-2fa',
        summary: 'Verify 2FA code',
        description: 'Verifies the 6-digit TOTP code from authenticator app',
        operationId: 'verify2fa',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/Verify2faRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Verification successful',
                content: new OA\JsonContent(ref: '#/components/schemas/Verify2faResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid code or not authenticated',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function verify2fa(Verify2FARequest $request): JsonResponse;

    #[OA\Post(
        path: '/auth/verify-recovery',
        summary: 'Verify recovery code',
        description: 'Uses a one-time recovery code to authenticate',
        operationId: 'verifyRecoveryCode',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['code'],
                properties: [
                    new OA\Property(property: 'code', type: 'string', example: 'ABCD-EFGH'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Verification successful',
                content: new OA\JsonContent(ref: '#/components/schemas/Verify2faResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid code or not authenticated',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function verifyRecoveryCode(VerifyRecovery2FARequest $request): JsonResponse;

    #[OA\Post(
        path: '/auth/logout',
        summary: 'Logout',
        description: 'Invalidates the current session',
        operationId: 'logout',
        tags: ['Authentication'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Logged out successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Logged out'),
                    ]
                )
            ),
        ]
    )]
    public function logout(Request $request): JsonResponse;

    #[OA\Get(
        path: '/auth/me',
        summary: 'Get current user',
        description: 'Returns the currently authenticated user and their 2FA status',
        operationId: 'me',
        tags: ['Authentication'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Current user information',
                content: new OA\JsonContent(ref: '#/components/schemas/MeResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'Not authenticated',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function me(Request $request): JsonResponse;
}
