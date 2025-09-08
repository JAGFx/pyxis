<?php

namespace App\Infrastructure\KnpPaginator\DTO;

interface PaginationInterface
{
    public function getPage(): int;

    public function getPageSize(): int;
}
