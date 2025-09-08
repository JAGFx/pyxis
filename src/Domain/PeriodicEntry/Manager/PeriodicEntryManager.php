<?php

namespace App\Domain\PeriodicEntry\Manager;

use App\Domain\PeriodicEntry\DTO\PeriodicEntrySearchCommand;
use App\Domain\PeriodicEntry\Entity\PeriodicEntry;
use App\Domain\PeriodicEntry\Repository\PeriodicEntryRepository;

class PeriodicEntryManager
{
    public function __construct(
        private readonly PeriodicEntryRepository $periodicEntryRepository,
    ) {
    }

    public function create(PeriodicEntry $entry, bool $flush = true): void
    {
        $this->periodicEntryRepository->create($entry, $flush);
    }

    public function update(PeriodicEntry $entry, bool $flush = true): void
    {
        $this->periodicEntryRepository->update($entry, $flush);
    }

    public function remove(PeriodicEntry $entry, bool $flush = true): void
    {
        $this->periodicEntryRepository->remove($entry, $flush);
    }

    /** @return PeriodicEntry[] */
    public function getPeriodicEntries(?PeriodicEntrySearchCommand $command = null): array
    {
        $command ??= new PeriodicEntrySearchCommand();

        /** @var PeriodicEntry[] $periodicEntries */
        $periodicEntries = $this->periodicEntryRepository
            ->getPeriodicEntriesQueryBuilder($command)
            ->getQuery()
            ->getResult();

        return $periodicEntries;
    }
}
