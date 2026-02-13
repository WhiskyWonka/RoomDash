<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use DateTimeImmutable;
use Domain\AuditLog\Entities\AuditLog;
use Domain\AuditLog\Ports\AuditLogRepositoryInterface;
use Domain\Auth\Ports\RootUserRepositoryInterface;
use Domain\Auth\Ports\TwoFactorServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Infrastructure\Auth\Models\RootUser;
use OpenApi\Attributes as OA;

class LoginController extends Controller
{
    public function __construct(
        private readonly RootUserRepositoryInterface $users,
        private readonly TwoFactorServiceInterface $twoFactor,
        private readonly AuditLogRepositoryInterface $auditLogRepository,
    ) {}

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
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Check if user exists with this email before verifying password
        // We need the Eloquent model to check is_active and email_verified_at
        $model = RootUser::where('email', $data['email'])->first();

        if (! $model || ! $model->password || ! \Illuminate\Support\Facades\Hash::check($data['password'], $model->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Check if email is verified (BR-009)
        if ($model->email_verified_at === null) {
            return response()->json([
                'message' => 'Email not verified',
                'code' => 'EMAIL_NOT_VERIFIED',
            ], 403);
        }

        // Check if account is active (BR-008)
        if (! $model->is_active) {
            return response()->json([
                'message' => 'Account deactivated',
                'code' => 'ACCOUNT_DEACTIVATED',
            ], 403);
        }

        $user = $model->toEntity();

        $request->session()->regenerate();
        Auth::guard('admin')->loginUsingId($user->id);

        $is2FaPending = $user->twoFactorEnabled; 
    
        $request->session()->put('2fa_pending', $is2FaPending);
        $request->session()->put('admin_user_id', $user->id);

        return response()->json([
            'user' => $user,
            'twoFactorEnabled' => $user->twoFactorEnabled,
            'requiresTwoFactorSetup' => ! $user->twoFactorEnabled,
        ]);
    }

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
    public function verify2fa(Request $request): JsonResponse
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
            return response()->json(['message' => '2FA not configured'], 400);
        }

        if (! $this->twoFactor->verify($secret, $data['code'])) {
            return response()->json(['message' => 'Invalid code'], 401);
        }

        $request->session()->forget('2fa_pending');
        $request->session()->put('2fa_verified', true);

        $user = $this->users->findById($userId);

        // Record audit log for successful login after 2FA
        $this->auditLogRepository->create(new AuditLog(
            id: Str::uuid()->toString(),
            userId: $userId,
            action: 'auth.login',
            entityType: null,
            entityId: null,
            oldValues: null,
            newValues: null,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            createdAt: new DateTimeImmutable(),
        ));

        return response()->json([
            'user' => $user,
            'verified' => true,
        ]);
    }

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
    public function verifyRecoveryCode(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => 'required|string',
        ]);

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

        return response()->json([
            'user' => $user,
            'verified' => true,
        ]);
    }

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
                createdAt: new DateTimeImmutable(),
            ));
        }

        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out']);
    }

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

        return response()->json([
            'user' => $user,
            'twoFactorVerified' => $request->session()->get('2fa_verified', false),
            'twoFactorPending' => $request->session()->get('2fa_pending', false),
        ]);
    }
}
