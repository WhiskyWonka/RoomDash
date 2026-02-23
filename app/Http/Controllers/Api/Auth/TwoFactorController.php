<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Docs\Endpoints\Api\Auth\TwoFAEndpoints;
use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\TwoFAConfirmRequest;
use Domain\Auth\Ports\TwoFactorServiceInterface;
use Domain\Auth\Ports\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TwoFactorController extends Controller implements TwoFAEndpoints
{
    use ApiResponse;

    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly TwoFactorServiceInterface $twoFactor,
    ) {}

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

        $data = ([
            'secret' => $secret,
            'qrCode' => $qrCode,
        ]);

        return $this->success($data, '2FA setup data retrieved successfully');
    }

    public function confirm(TwoFAConfirmRequest $request): JsonResponse
    {
        $data = $request->all();

        $userId = $request->session()->get('admin_user_id');
        if (! $userId) {
            return response()->json(['message' => 'Not authenticated'], 401);
        }

        $user = $this->users->findById($userId);
        if ($user?->twoFactorConfirmedAt) {
            return response()->json(['message' => '2FA is already enabled'], 400);
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

        $data = [
            'recoveryCodes' => $recoveryData['plain'],
            'enabled' => true,
        ];

        return $this->success($data, '2FA confirmed successfully');
    }

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

        $data = [
            'enabled' => $user->twoFactorEnabled,
            'confirmedAt' => $user->twoFactorConfirmedAt?->format('c'),
        ];

        return $this->success($data, '2FA status retrieved successfully');
    }
}
