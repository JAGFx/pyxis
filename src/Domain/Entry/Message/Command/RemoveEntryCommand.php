<?php

namespace App\Domain\Entry\Message\Command;

use App\Domain\Entry\Entity\Entry;
use App\Shared\Cqs\Message\Command\CommandInterface;

class RemoveEntryCommand implements CommandInterface
{
    public function __construct(
        private Entry $entry,
    ) {
    }

    public function getEntry(): Entry
    {
        return $this->entry;
    }

    public function setEntry(Entry $entry): RemoveEntryCommand
    {
        $this->entry = $entry;

        return $this;
    }
}
