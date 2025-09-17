<?php

namespace App\Infrastructure\KnpPaginator\DTO;

interface PaginationInterface
{
    public function getPage(): int;

    public function setPage(int $page): self;

    public function getPageSize(): int;
}
