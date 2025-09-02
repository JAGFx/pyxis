<?php

namespace App\Infrastructure\KnpPaginator\Model;

interface OrderableInterface
{
    public function getOrderBy(): ?string;

    public function getOrderDirection(): OrderEnum;
}
