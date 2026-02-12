<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Application\EmailVerification\DTOs\ResendVerificationRequest;
use Application\EmailVerification\UseCases\ResendVerificationUseCase;
use Application\RootUser\DTOs\CreateRootUserRequest;
use Application\RootUser\DTOs\DeleteRootUserRequest;
use Application\RootUser\DTOs\UpdateRootUserRequest;
use Application\RootUser\UseCases\CreateRootUserUseCase;
use Application\RootUser\UseCases\DeleteRootUserUseCase;
use Application\RootUser\UseCases\UpdateRootUserUseCase;
use DateTimeImmutable;
use Domain\AuditLog\Entities\AuditLog;
use Domain\AuditLog\Ports\AuditLogRepositoryInterface;
use Domain\Auth\Exceptions\AlreadyVerifiedException;
use Domain\Auth\Exceptions\LastActiveUserException;
use Domain\Auth\Exceptions\SelfDeletionException;
use Domain\Auth\Ports\RootUserRepositoryInterface;
use Domain\Auth\Services\LastActiveUserGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Infrastructure\Auth\Models\RootUser;

class RootUserController extends Controller
{
    public function __construct(
        private readonly CreateRootUserUseCase $createUseCase,
        private readonly UpdateRootUserUseCase $updateUseCase,
        private readonly DeleteRootUserUseCase $deleteUseCase,
        private readonly ResendVerificationUseCase $resendVerificationUseCase,
        private readonly RootUserRepositoryInterface $userRepository,
        private readonly LastActiveUserGuard $lastActiveGuard,
        private readonly AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', '15');
        $page = (int) $request->query('page', '1');

        $paginator = RootUser::orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $data = collect($paginator->items())->map(fn (RootUser $user) => [
            'id' => $user->id,
            'username' => $user->username,
            'firstName' => $user->first_name,
            'lastName' => $user->last_name,
            'email' => $user->email,
            'avatarUrl' => $user->avatar_path ? Storage::disk('public')->url($user->avatar_path) : null,
            'isActive' => $user->is_active,
            'emailVerifiedAt' => $user->email_verified_at?->toIso8601String(),
            'twoFactorEnabled' => $user->two_factor_enabled,
            'createdAt' => $user->created_at->toIso8601String(),
        ])->all();

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function show(string $id): JsonResponse
    {
        if (! Str::isUuid($id)) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $user = RootUser::find($id);

        if (! $user) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json([
            'data' => [
                'id' => $user->id,
                'username' => $user->username,
                'firstName' => $user->first_name,
                'lastName' => $user->last_name,
                'email' => $user->email,
                'avatarUrl' => $user->avatar_path ? Storage::disk('public')->url($user->avatar_path) : null,
                'isActive' => $user->is_active,
                'emailVerifiedAt' => $user->email_verified_at?->toIso8601String(),
                'twoFactorEnabled' => $user->two_factor_enabled,
                'createdAt' => $user->created_at->toIso8601String(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_-]+$/', 'unique:root_users,username'],
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:root_users,email',
        ]);

        $actorId = $request->session()->get('admin_user_id');

        $useCaseRequest = new CreateRootUserRequest(
            username: $data['username'],
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            email: $data['email'],
            actorId: $actorId,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        $user = $this->createUseCase->execute($useCaseRequest);

        return response()->json([
            'data' => $user->jsonSerialize(),
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        if (! Str::isUuid($id)) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $existingUser = RootUser::find($id);
        if (! $existingUser) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $data = $request->validate([
            'username' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_-]+$/', 'unique:root_users,username,' . $id],
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:root_users,email,' . $id,
        ]);

        $actorId = $request->session()->get('admin_user_id');

        $useCaseRequest = new UpdateRootUserRequest(
            id: $id,
            username: $data['username'],
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            email: $data['email'],
            actorId: $actorId,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        $updatedUser = $this->updateUseCase->execute($useCaseRequest);

        return response()->json([
            'data' => [
                'id' => $id,
                'username' => $updatedUser->username->value(),
                'firstName' => $updatedUser->firstName,
                'lastName' => $updatedUser->lastName,
                'email' => $updatedUser->email,
                'isActive' => $updatedUser->isActive,
                'emailVerifiedAt' => $updatedUser->emailVerifiedAt?->format('c'),
                'twoFactorEnabled' => $updatedUser->twoFactorEnabled,
                'createdAt' => $updatedUser->createdAt->format('c'),
            ],
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        if (! Str::isUuid($id)) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $existingUser = RootUser::find($id);
        if (! $existingUser) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $actorId = $request->session()->get('admin_user_id');

        try {
            $this->deleteUseCase->execute(new DeleteRootUserRequest(
                id: $id,
                actorId: $actorId,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            ));

            return response()->json(null, 204);
        } catch (SelfDeletionException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (LastActiveUserException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }
    }

    public function deactivate(Request $request, string $id): JsonResponse
    {
        $user = RootUser::find($id);
        if (! $user) {
            return response()->json(['message' => 'Not found'], 404);
        }

        try {
            $this->lastActiveGuard->assertCanDeactivate($id);
        } catch (LastActiveUserException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        $user->update(['is_active' => false]);

        $actorId = $request->session()->get('admin_user_id');

        $this->auditLogRepository->create(new AuditLog(
            id: Str::uuid()->toString(),
            userId: $actorId,
            action: 'root_user.deactivated',
            entityType: 'root_user',
            entityId: $id,
            oldValues: ['is_active' => true],
            newValues: ['is_active' => false],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            createdAt: new DateTimeImmutable(),
        ));

        return response()->json(['message' => 'User deactivated']);
    }

    public function activate(Request $request, string $id): JsonResponse
    {
        $user = RootUser::find($id);
        if (! $user) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $user->update(['is_active' => true]);

        $actorId = $request->session()->get('admin_user_id');

        $this->auditLogRepository->create(new AuditLog(
            id: Str::uuid()->toString(),
            userId: $actorId,
            action: 'root_user.activated',
            entityType: 'root_user',
            entityId: $id,
            oldValues: ['is_active' => false],
            newValues: ['is_active' => true],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            createdAt: new DateTimeImmutable(),
        ));

        return response()->json(['message' => 'User activated']);
    }

    public function resendVerification(Request $request, string $id): JsonResponse
    {
        $user = RootUser::find($id);
        if (! $user) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $actorId = $request->session()->get('admin_user_id');

        try {
            $this->resendVerificationUseCase->execute(new ResendVerificationRequest(
                userId: $id,
                actorId: $actorId,
            ));

            return response()->json(['message' => 'Verification email sent']);
        } catch (AlreadyVerifiedException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }
    }

    public function uploadAvatar(Request $request, string $id): JsonResponse
    {
        if (! Str::isUuid($id)) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $user = RootUser::find($id);
        if (! $user) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $request->validate([
            'avatar' => 'required|image|mimes:webp,png,jpg,jpeg|max:1024|dimensions:ratio=1/1',
        ]);

        $file = $request->file('avatar');
        $path = $file->store('avatars', 'public');

        // Delete old avatar if exists
        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->update(['avatar_path' => $path]);

        $actorId = $request->session()->get('admin_user_id');

        $this->auditLogRepository->create(new AuditLog(
            id: Str::uuid()->toString(),
            userId: $actorId,
            action: 'root_user.avatar_updated',
            entityType: 'root_user',
            entityId: $id,
            oldValues: null,
            newValues: ['avatar_path' => $path],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            createdAt: new DateTimeImmutable(),
        ));

        return response()->json([
            'data' => [
                'avatarUrl' => Storage::disk('public')->url($path),
            ],
        ]);
    }

    public function deleteAvatar(Request $request, string $id): JsonResponse
    {
        $user = RootUser::find($id);
        if (! $user) {
            return response()->json(['message' => 'Not found'], 404);
        }

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->update(['avatar_path' => null]);

        return response()->json(['message' => 'Avatar deleted']);
    }
}
