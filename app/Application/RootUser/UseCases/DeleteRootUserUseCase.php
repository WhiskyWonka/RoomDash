<?php

declare(strict_types=1);

namespace Application\RootUser\UseCases;

use Application\RootUser\DTOs\DeleteRootUserRequest;
use DateTimeImmutable;
use Domain\AuditLog\Entities\AuditLog;
use Domain\AuditLog\Ports\AuditLogRepositoryInterface;
use Domain\Auth\Ports\RootUserRepositoryInterface;
use Domain\Auth\Services\LastActiveUserGuard;
use Illuminate\Support\Str;

class DeleteRootUserUseCase
{
    public function __construct(
        private readonly RootUserRepositoryInterface $userRepository,
        private readonly LastActiveUserGuard $lastActiveGuard,
        private readonly AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    public function execute(DeleteRootUserRequest $request): void
    {
        $user = $this->userRepository->findById($request->id);

        // Check self-deletion
        $user->assertCannotDeleteSelf($request->actorId);

        // Check last active user protection
        $this->lastActiveGuard->assertCanDelete($request->id);

        // Delete user
        $this->userRepository->delete($request->id);

        // Record audit log
        $this->auditLogRepository->create(new AuditLog(
            id: Str::uuid()->toString(),
            userId: $request->actorId,
            action: 'root_user.deleted',
            entityType: 'root_user',
            entityId: $request->id,
            oldValues: [
                'username' => $user->username->value(),
                'email' => $user->email,
            ],
            newValues: null,
            ipAddress: $request->ipAddress,
            userAgent: $request->userAgent,
            createdAt: new DateTimeImmutable(),
        ));
    }
}
