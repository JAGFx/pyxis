<?php

namespace App\Domain\PeriodicEntry\Message\Command;

use App\Domain\PeriodicEntry\Entity\PeriodicEntry;
use App\Shared\Cqs\Message\Command\CommandInterface;

class PeriodicEntryRemoveCommand implements CommandInterface
{
    public function __construct(
        private PeriodicEntry $periodicEntry,
    ) {
    }

    public function getPeriodicEntry(): PeriodicEntry
    {
        return $this->periodicEntry;
    }

    public function setPeriodicEntry(PeriodicEntry $periodicEntry): PeriodicEntryRemoveCommand
    {
        $this->periodicEntry = $periodicEntry;

        return $this;
    }
}
