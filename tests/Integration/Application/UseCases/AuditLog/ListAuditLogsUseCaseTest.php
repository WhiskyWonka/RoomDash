<?php

declare(strict_types=1);

use Application\AuditLog\UseCases\ListAuditLogsUseCase;
use Application\AuditLog\DTOs\ListAuditLogsRequest;
use Domain\AuditLog\Ports\AuditLogRepositoryInterface;

it('returns paginated results sorted by newest first', function () {
    // Arrange
    $auditLogRepository = Mockery::mock(AuditLogRepositoryInterface::class);

    $paginatedResult = [
        'data' => [
            ['id' => 'log-2', 'action' => 'root_user.updated', 'created_at' => '2026-01-02'],
            ['id' => 'log-1', 'action' => 'root_user.created', 'created_at' => '2026-01-01'],
        ],
        'total' => 2,
        'per_page' => 25,
        'current_page' => 1,
    ];

    $auditLogRepository->shouldReceive('findPaginated')
        ->once()
        ->with(Mockery::on(function ($filters) {
            return $filters['per_page'] === 25 && $filters['page'] === 1;
        }))
        ->andReturn($paginatedResult);

    $useCase = new ListAuditLogsUseCase($auditLogRepository);

    $request = new ListAuditLogsRequest(
        page: 1,
        perPage: 25,
        userId: null,
        action: null,
        entityType: null,
        from: null,
        to: null,
    );

    // Act
    $result = $useCase->execute($request);

    // Assert
    expect($result['total'])->toBe(2);
    expect($result['data'])->toHaveCount(2);
    expect($result['data'][0]['id'])->toBe('log-2'); // Newest first
});

it('filters by user id action entity type and date range', function () {
    // Arrange
    $auditLogRepository = Mockery::mock(AuditLogRepositoryInterface::class);

    $auditLogRepository->shouldReceive('findPaginated')
        ->once()
        ->with(Mockery::on(function ($filters) {
            return $filters['user_id'] === 'specific-user-uuid'
                && $filters['action'] === 'root_user.created'
                && $filters['entity_type'] === 'root_user'
                && $filters['from'] === '2026-01-01'
                && $filters['to'] === '2026-01-31';
        }))
        ->andReturn(['data' => [], 'total' => 0, 'per_page' => 25, 'current_page' => 1]);

    $useCase = new ListAuditLogsUseCase($auditLogRepository);

    $request = new ListAuditLogsRequest(
        page: 1,
        perPage: 25,
        userId: 'specific-user-uuid',
        action: 'root_user.created',
        entityType: 'root_user',
        from: '2026-01-01',
        to: '2026-01-31',
    );

    // Act
    $result = $useCase->execute($request);

    // Assert
    expect($result['total'])->toBe(0);
});
