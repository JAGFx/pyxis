<?php

namespace App\Domain\PeriodicEntry\Model;

use App\Domain\Entry\Model\EntryTypeEnum;

class PeriodicEntrySearchCommand
{
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
