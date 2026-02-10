<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Domain\Auth\Ports\AdminUserRepositoryInterface;
use Domain\Auth\Ports\TwoFactorServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class TwoFactorController extends Controller
{
    public function __construct(
        private readonly AdminUserRepositoryInterface $users,
        private readonly TwoFactorServiceInterface $twoFactor,
    ) {}

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
    public function setup(Request $request): JsonResponse
    {
        $userId = $request->session()->get('admin_user_id');
        if (! $userId) {
            return response()->json(['message' => 'Not authenticated'], 401);
        }

        $user = $this->users->findById($userId);
        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $secret = $this->users->getTwoFactorSecret($userId);
        if (! $secret) {
            $secret = $this->twoFactor->generateSecret();
            $this->users->setTwoFactorSecret($userId, $secret);
        }

        $qrCode = $this->twoFactor->generateQrCodeDataUri($secret, $user->email);

        return response()->json([
            'secret' => $secret,
            'qrCode' => $qrCode,
        ]);
    }

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
    public function confirm(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $userId = $request->session()->get('admin_user_id');
        if (! $userId) {
            return response()->json(['message' => 'Not authenticated'], 401);
        }

        $secret = $this->users->getTwoFactorSecret($userId);
        if (! $secret) {
            return response()->json(['message' => '2FA not set up'], 400);
        }

        if (! $this->twoFactor->verify($secret, $data['code'])) {
            return response()->json(['message' => 'Invalid code'], 400);
        }

        $recoveryData = $this->twoFactor->generateRecoveryCodes();
        $this->users->setRecoveryCodes($userId, $recoveryData['hashed']);
        $this->users->enableTwoFactor($userId);

        $request->session()->forget('2fa_pending');
        $request->session()->put('2fa_verified', true);

        return response()->json([
            'recoveryCodes' => $recoveryData['plain'],
            'enabled' => true,
        ]);
    }

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
    public function status(Request $request): JsonResponse
    {
        $userId = $request->session()->get('admin_user_id');
        if (! $userId) {
            return response()->json(['message' => 'Not authenticated'], 401);
        }

        $user = $this->users->findById($userId);
        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json([
            'enabled' => $user->twoFactorEnabled,
            'confirmedAt' => $user->twoFactorConfirmedAt?->format('c'),
        ]);
    }
}
