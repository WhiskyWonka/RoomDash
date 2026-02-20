<?php

declare(strict_types=1);

use Application\AuditLog\DTOs\RecordAuditLogRequest;
use Application\AuditLog\UseCases\RecordAuditLogUseCase;
use Domain\AuditLog\Entities\AuditLog;
use Domain\AuditLog\Ports\AuditLogRepositoryInterface;
use Domain\Shared\Ports\UuidGeneratorInterface;

it('records audit log with correct data', function () {
    // Arrange
    $auditLogRepository = Mockery::mock(AuditLogRepositoryInterface::class);
    $uuidGenerator = Mockery::mock(UuidGeneratorInterface::class);

    $uuidGenerator->shouldReceive('generate')
        ->once()
        ->andReturn('generated-uuid');

    $auditLogRepository->shouldReceive('create')
        ->once()
        ->with(Mockery::on(function (AuditLog $log) {
            return $log->userId === 'actor-uuid'
                && $log->action === 'root_user.created'
                && $log->entityType === 'root_user'
                && $log->entityId === 'new-user-uuid'
                && $log->ipAddress === '127.0.0.1'
                && $log->userAgent === 'TestAgent/1.0';
        }));

    $useCase = new RecordAuditLogUseCase(
        $auditLogRepository,
        $uuidGenerator, );

    $request = new RecordAuditLogRequest(
        actorId: 'actor-uuid',
        action: 'root_user.created',
        entityType: 'root_user',
        entityId: 'new-user-uuid',
        oldValues: null,
        newValues: ['email' => 'john@example.com'],
        ipAddress: '127.0.0.1',
        userAgent: 'TestAgent/1.0',
    );

    // Act
    $useCase->execute($request);
});
