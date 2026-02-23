<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Docs\Endpoints\Api\Auth\LoginEndpoints;
use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\Verify2FARequest;
use App\Http\Requests\Auth\VerifyRecovery2FARequest;
use Application\Login\DTOs\UserLoginDTO;
use Application\Login\UseCases\LoginUseCase as UseCasesLoginUseCase;
use DateTimeImmutable;
use Domain\AuditLog\Entities\AuditLog;
use Domain\AuditLog\Ports\AuditLogRepositoryInterface;
use Domain\Auth\Ports\TwoFactorServiceInterface;
use Domain\Auth\Ports\UserRepositoryInterface;
use Domain\Shared\Ports\PasswordHasherInterface;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LoginController extends Controller implements LoginEndpoints
{
    use ApiResponse;

    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly TwoFactorServiceInterface $twoFactor,
        private readonly AuditLogRepositoryInterface $auditLogRepository,
        private readonly PasswordHasherInterface $hasherService,
        private readonly UseCasesLoginUseCase $loginUseCase,
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $userDTO = new UserLoginDTO(
                email: $request->input('email'),
                password: $request->input('password'),
            );

            $user = $this->loginUseCase->execute($userDTO);

            $request->session()->regenerate();
            Auth::guard('admin')->loginUsingId($user->id);

            $request->session()->put('2fa_verified', false);
            $request->session()->put('admin_user_id', $user->id);
            $request->session()->put('2fa_pending', true);

            $notConfirmed = $user->twoFactorConfirmedAt ? false : true;

            if ($notConfirmed) {
                $data = [
                    'user' => $user->jsonSerialize(),
                    'twoFactorRequired' => true,
                    'requiresSetup' => true,
                ];

                return $this->success($data, '2FA Setup Required');
            }

            $data = [
                'user' => $user->jsonSerialize(),
                'twoFactorRequired' => true,
                'requiresSetup' => false,
            ];

            return $this->success($data, 'Login success', 200);

        } catch (DomainException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }

    }

    public function verify2fa(Verify2FARequest $request): JsonResponse
    {
        $data = $request->validated();

        $userId = $request->session()->get('admin_user_id');
        if (! $userId) {
            return response()->json(['message' => 'Not authenticated'], 401);
        }

        $secret = $this->users->getTwoFactorSecret($userId);
        if (! $secret) {
            return response()->json(['message' => '2FA not configured'], 400);
        }

        if (! $this->twoFactor->verify($secret, $data['code'])) {
            return response()->json(['message' => 'Invalid code'], 401);
        }

        $request->session()->put('2fa_verified', true);
        $request->session()->put('2fa_pending', false);

        $user = $this->users->findById($userId);

        $response_data = [
            'user' => $user,
            'verified' => true,
        ];

        return $this->success($response_data, '2FA verified successfully', 200);
    }

    public function verifyRecoveryCode(VerifyRecovery2FARequest $request): JsonResponse
    {
        $data = $request->validated();

        $userId = $request->session()->get('admin_user_id');
        if (! $userId) {
            return response()->json(['message' => 'Not authenticated'], 401);
        }

        if (! $this->users->useRecoveryCode($userId, $data['code'])) {
            return response()->json(['message' => 'Invalid recovery code'], 401);
        }

        $request->session()->forget('2fa_pending');
        $request->session()->put('2fa_verified', true);

        $user = $this->users->findById($userId);

        $response_data = [
            'user' => $user,
            'verified' => true,
        ];

        return $this->success($response_data, 'Recovery code verified successfully', 200);
    }

    public function logout(Request $request): JsonResponse
    {
        $userId = $request->session()->get('admin_user_id');

        // Record audit log before session invalidation
        if ($userId) {
            $this->auditLogRepository->create(new AuditLog(
                id: Str::uuid()->toString(),
                userId: $userId,
                action: 'auth.logout',
                entityType: null,
                entityId: null,
                oldValues: null,
                newValues: null,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
                createdAt: new DateTimeImmutable,
            ));
        }

        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $this->success([], 'Logged out', 200);
    }

    public function me(Request $request): JsonResponse
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
            'user' => $user,
            'twoFactorVerified' => $request->session()->get('2fa_verified', false),
            'twoFactorPending' => $request->session()->get('2fa_pending', false),
        ];

        return $this->success($data, 'User retrieved successfully', 200);
    }
}
