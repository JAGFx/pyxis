<?php

namespace App\Domain\Entry\Message\Query;

use App\Domain\Account\Entity\Account;
use App\Domain\Budget\Entity\Budget;
use App\Domain\Entry\Entity\EntryFlagEnum;
use App\Domain\Entry\Entity\EntryTypeEnum;
use App\Infrastructure\KnpPaginator\DTO\OrderableInterface;
use App\Infrastructure\KnpPaginator\DTO\OrderableTrait;
use App\Infrastructure\KnpPaginator\DTO\PaginableTrait;
use App\Infrastructure\KnpPaginator\DTO\PaginationInterface;
use App\Shared\Cqs\Message\Query\QueryInterface;
use DateTimeImmutable;

class EntrySearchQuery implements PaginationInterface, OrderableInterface, QueryInterface
{
    use PaginableTrait;
    use OrderableTrait;
    public const int WITHOUT_FLAG_VALUE = -1;

    public function __construct(
        private ?Account $account = null,
        private ?DateTimeImmutable $startDate = null,
        private ?DateTimeImmutable $endDate = null,
        private ?string $name = null,
        private ?EntryTypeEnum $type = null,
        private ?Budget $budget = null,
        /**
         * @var array<int|EntryFlagEnum>
         */
        private array $flags = [self::WITHOUT_FLAG_VALUE],
    ) {
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): EntrySearchQuery
    {
        $this->account = $account;

        return $this;
    }

    public function getStartDate(): ?DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(?DateTimeImmutable $startDate): EntrySearchQuery
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(?DateTimeImmutable $endDate): EntrySearchQuery
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): EntrySearchQuery
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?EntryTypeEnum
    {
        return $this->type;
    }

    public function setType(?EntryTypeEnum $type): EntrySearchQuery
    {
        $this->type = $type;

        return $this;
    }

    public function getBudget(): ?Budget
    {
        return $this->budget;
    }

    public function setBudget(?Budget $budget): EntrySearchQuery
    {
        $this->budget = $budget;

        return $this;
    }

    /**
     * @return array<int|EntryFlagEnum>
     */
    public function getFlags(): array
    {
        return $this->flags;
    }

    /**
     * @param array<int|EntryFlagEnum> $flags
     */
    public function setFlags(array $flags): EntrySearchQuery
    {
        $this->flags = $flags;

        return $this;
    }
}
