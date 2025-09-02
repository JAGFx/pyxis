<?php

namespace App\Infrastructure\KnpPaginator\Model;

trait OrderableTrait
{
    protected ?string $orderBy          = null;
    protected OrderEnum $orderDirection = OrderEnum::ASC;

    public function getOrderBy(): ?string
    {
        return $this->orderBy;
    }

    public function setOrderBy(?string $orderBy): self
    {
        $this->orderBy = $orderBy;

        return $this;
    }

    public function getOrderDirection(): OrderEnum
    {
        return $this->orderDirection;
    }

    public function setOrderDirection(OrderEnum $orderDirection): self
    {
        $this->orderDirection = $orderDirection;

        return $this;
    }
}
