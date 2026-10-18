<?php

namespace App\Domain\PeriodicEntry\Message\Query\FindPeriodicEntries;

use App\Domain\Entry\Entity\EntryTypeEnum;
use App\Infrastructure\KnpPaginator\DTO\OrderableInterface;
use App\Infrastructure\KnpPaginator\DTO\OrderableTrait;
use App\Shared\Cqs\Message\Query\QueryInterface;

/**
 * @see FindPeriodicEntriesHandler
 */
class FindPeriodicEntriesQuery implements OrderableInterface, QueryInterface
{
    use OrderableTrait;

    public function __construct(
        private ?EntryTypeEnum $entryTypeEnum = null,
        private ?string $name = null,
        // Like budgets: only the enabled ones by default, null for all
        private ?bool $enabled = true,
    ) {
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(?bool $enabled): FindPeriodicEntriesQuery
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getEntryTypeEnum(): ?EntryTypeEnum
    {
        return $this->entryTypeEnum;
    }

    public function setEntryTypeEnum(?EntryTypeEnum $entryTypeEnum): FindPeriodicEntriesQuery
    {
        $this->entryTypeEnum = $entryTypeEnum;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): FindPeriodicEntriesQuery
    {
        $this->name = $name;

        return $this;
    }
}
