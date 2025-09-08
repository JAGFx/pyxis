<?php

namespace App\Infrastructure\KnpPaginator\DTO;

interface OrderableInterface
{
    public function getOrderBy(): ?string;

    public function getOrderDirection(): OrderEnum;
}
