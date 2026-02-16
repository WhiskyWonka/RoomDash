<?php
namespace Domain\Shared\DTOs;
readonly class PaginatedResult
{
    public function __construct(
        public array $items,
        public int $total,
        public int $perPage,
        public int $currentPage
    ) {}
}