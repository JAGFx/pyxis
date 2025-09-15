<?php

namespace App\Domain\PeriodicEntry\Request;

use App\Domain\Entry\Entity\EntryTypeEnum;
use App\Infrastructure\KnpPaginator\DTO\OrderableInterface;
use App\Infrastructure\KnpPaginator\DTO\OrderableTrait;

class PeriodicEntrySearchRequest implements OrderableInterface
{
    use OrderableTrait;

    public function __construct(
        private ?EntryTypeEnum $entryTypeEnum = null,
        private ?string $name = null,
    ) {
    }

    public function getEntryTypeEnum(): ?EntryTypeEnum
    {
        return $this->entryTypeEnum;
    }

    public function setEntryTypeEnum(?EntryTypeEnum $entryTypeEnum): PeriodicEntrySearchRequest
    {
        $this->entryTypeEnum = $entryTypeEnum;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): PeriodicEntrySearchRequest
    {
        $this->name = $name;

        return $this;
    }
}
