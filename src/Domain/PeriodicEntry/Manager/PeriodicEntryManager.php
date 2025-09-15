<?php

namespace App\Domain\PeriodicEntry\Manager;

use App\Domain\PeriodicEntry\DTO\PeriodicEntrySearchCommand;
use App\Domain\PeriodicEntry\Entity\PeriodicEntry;
use App\Domain\PeriodicEntry\Repository\PeriodicEntryRepository;
use Doctrine\ORM\EntityManagerInterface;

class PeriodicEntryManager
{
    public function __construct(
        private readonly PeriodicEntryRepository $repository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function create(PeriodicEntry $entity, bool $flush = true): void
    {
        $this->repository->create($entity);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function update(bool $flush = true): void
    {
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function remove(PeriodicEntry $entity, bool $flush = true): void
    {
        $this->repository->remove($entity);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    /** @return PeriodicEntry[] */
    public function getPeriodicEntries(?PeriodicEntrySearchCommand $command = null): array
    {
        $command ??= new PeriodicEntrySearchCommand();

        /** @var PeriodicEntry[] $periodicEntries */
        $periodicEntries = $this->repository
            ->getPeriodicEntriesQueryBuilder($command)
            ->getQuery()
            ->getResult();

        return $periodicEntries;
    }
}
