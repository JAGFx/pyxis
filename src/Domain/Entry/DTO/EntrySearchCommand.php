<?php

namespace App\Domain\Entry\DTO;

use App\Domain\Account\Entity\Account;
use App\Domain\Budget\Entity\Budget;
use App\Domain\Entry\Entity\EntryTypeEnum;
use App\Infrastructure\KnpPaginator\DTO\OrderableInterface;
use App\Infrastructure\KnpPaginator\DTO\OrderableTrait;
use App\Infrastructure\KnpPaginator\DTO\PaginableTrait;
use App\Infrastructure\KnpPaginator\DTO\PaginationInterface;
use DateTimeImmutable;

class EntrySearchCommand implements PaginationInterface, OrderableInterface
{
    use PaginableTrait;
    use OrderableTrait;

    public function __construct(
        private ?Account $account = null,
        private ?DateTimeImmutable $startDate = null,
        private ?DateTimeImmutable $endDate = null,
        private ?string $name = null,
        private ?EntryTypeEnum $type = null,
        private ?Budget $budget = null,
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): EntrySearchCommand
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?EntryTypeEnum
    {
        return $this->type;
    }

    public function setType(?EntryTypeEnum $type): EntrySearchCommand
    {
        $this->type = $type;

        return $this;
    }

    public function getBudget(): ?Budget
    {
        return $this->budget;
    }

    public function setBudget(?Budget $budget): EntrySearchCommand
    {
        $this->budget = $budget;

        return $this;
    }
}
