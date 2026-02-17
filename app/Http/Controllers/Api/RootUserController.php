<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\RootUser\RootUserStoreRequest;
use App\Http\Requests\RootUser\RootUserUpdateRequest;
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
use Domain\Auth\Entities\RootUser;
use Domain\Auth\Exceptions\AlreadyVerifiedException;
use Domain\Auth\Exceptions\LastActiveUserException;
use Domain\Auth\Exceptions\SelfDeletionException;
use Domain\Auth\Ports\RootUserRepositoryInterface;
use Domain\Auth\Services\LastActiveUserGuard;
use Domain\Shared\Ports\UuidGeneratorInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

// use Illuminate\Support\Str;

// use Infrastructure\Auth\Models\RootUser;
// TODO: interface de documentacion swagger?
class RootUserController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly CreateRootUserUseCase $createUseCase,
        private readonly UpdateRootUserUseCase $updateUseCase,
        private readonly DeleteRootUserUseCase $deleteUseCase,
        private readonly ResendVerificationUseCase $resendVerificationUseCase,
        private readonly RootUserRepositoryInterface $userRepository,
        private readonly LastActiveUserGuard $lastActiveGuard,
        private readonly AuditLogRepositoryInterface $auditLogRepository,
        private readonly UuidGeneratorInterface $uuidGenerator,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', '15');
        $page = (int) $request->query('page', '1');

        $paginator = $this->userRepository->listPaginated(
            page: $page,
            perPage: $perPage,
            sortField: $request->query('sort_field', 'created_at'),
            sortDirection: $request->query('sort_direction', 'desc'),
        );

        $data = collect($paginator->items)->map(fn (RootUser $user) => [
            'id' => $user->id,
            'username' => $user->username,
            'firstName' => $user->firstName,
            'lastName' => $user->lastName,
            'email' => $user->email,
            'avatarUrl' => $user->avatarPath ? Storage::url($user->avatarPath) : null, // TODO: storage tiene que ser infra. poner una regla para que salte
            'isActive' => $user->isActive,
            'createdAt' => $user->createdAt->format('Y-m-d H:i:s'),
        ])->all();

        return $this->success(data: [
            'users' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage,
                'per_page' => $paginator->perPage,
                'total' => $paginator->total,
            ],
        ]);
    }

    public function show(string $id): JsonResponse
    {
        // dd($id);
        if (! $this->uuidGenerator->validate($id)) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $user = $this->userRepository->findById($id);

        if (! $user) {
            return response()->json(['message' => 'Not found'], 404);
        }

        // TODO: estos datas deberian estar en una capa de presenters
        return $this->success(data: [
            'id' => $user->id,
            'username' => $user->username,
            'firstName' => $user->firstName,
            'lastName' => $user->lastName,
            'email' => $user->email,
            'avatarUrl' => $user->avatarPath ? Storage::url($user->avatarPath) : null,
            'isActive' => $user->isActive,
            'emailVerifiedAt' => $user->emailVerifiedAt?->format('Y-m-d H:i:s'),
            'twoFactorEnabled' => $user->twoFactorEnabled,
            'createdAt' => $user->createdAt->format('Y-m-d H:i:s'),
        ]);
    }

    public function store(RootUserStoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        $actorId = $request->session()->get('admin_user_id');

        $useCaseRequest = new CreateRootUserRequest(
            username: $data['username'],
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            email: $data['email'],
            password: $data['password'],
            actorId: $actorId,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        $user = $this->createUseCase->execute($useCaseRequest);

        $request->merge(['manipulated_entity' => 'userroot']);

        return $this->success(data: $user, message: 'Root user created successfully', code: 201);

    }

    public function update(RootUserUpdateRequest $request, string $id): JsonResponse
    {
        $existingUser = $this->userRepository->existsById($id);
        if (! $existingUser) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $data = $request->validated();

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

        $request->merge(['manipulated_entity' => 'userroot']);

        return $this->success(data: $updatedUser, message: 'Root user updated successfully', code: 200);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        if (! Str::isUuid($id)) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $existingUser = $this->userRepository->findById($id);
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
        $user = $this->userRepository->findById($id);
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
            createdAt: new DateTimeImmutable,
        ));

        return response()->json(['message' => 'User deactivated']);
    }

    public function activate(Request $request, string $id): JsonResponse
    {
        $user = $this->userRepository->findById($id);
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
            createdAt: new DateTimeImmutable,
        ));

        return response()->json(['message' => 'User activated']);
    }

    public function resendVerification(Request $request, string $id): JsonResponse
    {
        $user = $this->userRepository->findById($id);
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

        $user = $this->userRepository->findById($id);
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
            createdAt: new DateTimeImmutable,
        ));

        return response()->json([
            'data' => [
                'avatarUrl' => Storage::url($path),
            ],
        ]);
    }

    public function deleteAvatar(Request $request, string $id): JsonResponse
    {
        $user = $this->userRepository->findById($id);
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
