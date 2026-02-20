<?php

namespace App\Docs\Endpoints\Api\Auth;

use App\Http\Requests\Auth\TwoFAConfirmRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

interface TwoFAEndpoints
{
    #[OA\Get(
        path: '/auth/2fa/setup',
        summary: 'Get 2FA setup information',
        description: 'Returns the secret key and QR code for setting up 2FA',
        operationId: 'get2faSetup',
        tags: ['Two-Factor Authentication'],
        responses: [
            new OA\Response(
                response: 200,
                description: '2FA setup information',
                content: new OA\JsonContent(ref: '#/components/schemas/TwoFactorSetupResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'Not authenticated',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function setup(Request $request): JsonResponse;

    #[OA\Post(
        path: '/auth/2fa/confirm',
        summary: 'Confirm 2FA setup',
        description: 'Verifies the code and enables 2FA, returns recovery codes',
        operationId: 'confirm2fa',
        tags: ['Two-Factor Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/Verify2faRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: '2FA enabled successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/TwoFactorConfirmResponse')
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid code or 2FA not set up',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'Not authenticated',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function confirm(TwoFAConfirmRequest $request): JsonResponse;

    #[OA\Get(
        path: '/auth/2fa/status',
        summary: 'Get 2FA status',
        description: 'Returns whether 2FA is enabled for the current user',
        operationId: 'get2faStatus',
        tags: ['Two-Factor Authentication'],
        responses: [
            new OA\Response(
                response: 200,
                description: '2FA status',
                content: new OA\JsonContent(ref: '#/components/schemas/TwoFactorStatusResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'Not authenticated',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function status(Request $request): JsonResponse;
}
