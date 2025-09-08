<?php

namespace App\Domain\PeriodicEntry\DTO;

use App\Domain\Entry\Entity\EntryTypeEnum;
use App\Infrastructure\KnpPaginator\DTO\OrderableInterface;
use App\Infrastructure\KnpPaginator\DTO\OrderableTrait;

class PeriodicEntrySearchCommand implements OrderableInterface
{
    use OrderableTrait;

    public function __construct(
        private ?EntryTypeEnum $entryTypeEnum = null,
    ) {
    }

    public function getEntryTypeEnum(): ?EntryTypeEnum
    {
        return $this->entryTypeEnum;
    }

    public function setEntryTypeEnum(?EntryTypeEnum $entryTypeEnum): PeriodicEntrySearchCommand
    {
        $this->entryTypeEnum = $entryTypeEnum;

        return $this;
    }
}
