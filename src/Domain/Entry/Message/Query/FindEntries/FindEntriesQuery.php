<?php

namespace App\Domain\Entry\Message\Query\FindEntries;

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

/**
 * @see FindEntriesHandler
 */
class FindEntriesQuery implements PaginationInterface, OrderableInterface, QueryInterface
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

    public function setAccount(?Account $account): FindEntriesQuery
    {
        $this->account = $account;

        return $this;
    }

    public function getStartDate(): ?DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(?DateTimeImmutable $startDate): FindEntriesQuery
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(?DateTimeImmutable $endDate): FindEntriesQuery
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): FindEntriesQuery
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?EntryTypeEnum
    {
        return $this->type;
    }

    public function setType(?EntryTypeEnum $type): FindEntriesQuery
    {
        $this->type = $type;

        return $this;
    }

    public function getBudget(): ?Budget
    {
        return $this->budget;
    }

    public function setBudget(?Budget $budget): FindEntriesQuery
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
    public function setFlags(array $flags): FindEntriesQuery
    {
        $this->flags = $flags;

        return $this;
    }
}
