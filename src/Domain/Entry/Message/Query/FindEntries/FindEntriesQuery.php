<?php

namespace App\Domain\Entry\Message\Query\FindEntries;

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
        private ?int $accountId = null,
        private ?DateTimeImmutable $startDate = null,
        private ?DateTimeImmutable $endDate = null,
        private ?string $name = null,
        private ?EntryTypeEnum $type = null,
        private ?int $budgetId = null,
        /**
         * @var array<int|EntryFlagEnum>
         */
        private array $flags = [self::WITHOUT_FLAG_VALUE],
    ) {
    }

    public function getAccountId(): ?int
    {
        return $this->accountId;
    }

    public function setAccountId(?int $accountId): FindEntriesQuery
    {
        $this->accountId = $accountId;

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

    public function getBudgetId(): ?int
    {
        return $this->budgetId;
    }

    public function setBudgetId(?int $budgetId): FindEntriesQuery
    {
        $this->budgetId = $budgetId;

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
