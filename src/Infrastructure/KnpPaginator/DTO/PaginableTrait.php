<?php

namespace App\Infrastructure\KnpPaginator\DTO;

use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

trait PaginableTrait
{
    #[GreaterThanOrEqual(1)]
    protected int $page     = 1;

    protected int $pageSize = 6;

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): self
    {
        $this->page = $page;

        return $this;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function setPageSize(int $pageSize): self
    {
        $this->pageSize = $pageSize;

        return $this;
    }
}
