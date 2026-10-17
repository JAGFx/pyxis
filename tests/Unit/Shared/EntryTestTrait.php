<?php

namespace App\Tests\Unit\Shared;

use App\Domain\Entry\Entity\Entry;
use DateTimeImmutable;

trait EntryTestTrait
{
    private function generateEntry(array $data = []): Entry
    {
        $entry = (new Entry())
            ->setName($data['entryName'] ?? 'An entry')
            ->setAmount($data['entryAmount'] ?? 0);

        $entry->setCreatedAt($data['entryCreatedAt'] ?? new DateTimeImmutable());

        if (isset($data['budget'])) {
            $entry->setBudget($data['budget']);
        }

        return $entry;
    }
}
