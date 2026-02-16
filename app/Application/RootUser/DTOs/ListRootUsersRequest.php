<?php

declare(strict_types=1);

namespace Application\RootUser\DTOs;

final class ListRootUsersRequest
{
    public function __construct(
        public int $page = 1,
        public int $perPage = 15,
        public string $sortField = 'created_at',
        public string $sortDirection = 'desc'
    ) {}
}