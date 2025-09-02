<?php

namespace App\Domain\Entry\Model;

use App\Domain\Account\Entity\Account;
use App\Infrastructure\KnpPaginator\Model\OrderableInterface;
use App\Infrastructure\KnpPaginator\Model\OrderableTrait;
use App\Infrastructure\KnpPaginator\Model\PaginableTrait;
use App\Infrastructure\KnpPaginator\Model\PaginationInterface;
use DateTimeImmutable;

class EntrySearchCommand implements PaginationInterface, OrderableInterface
{
    use PaginableTrait;
    use OrderableTrait;

    public function __construct(
        private ?Account $account = null,
        private ?DateTimeImmutable $startDate = null,
        private ?DateTimeImmutable $endDate = null,
    ) {
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): EntrySearchCommand
    {
        $this->account = $account;

        return $this;
    }

    public function getStartDate(): ?DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(?DateTimeImmutable $startDate): EntrySearchCommand
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(?DateTimeImmutable $endDate): EntrySearchCommand
    {
        $this->endDate = $endDate;

        return $this;
    }
}
