<?php

namespace App\Domain\PeriodicEntry\Manager;

use App\Domain\PeriodicEntry\Entity\PeriodicEntry;
use App\Domain\PeriodicEntry\Repository\PeriodicEntryRepository;
use App\Domain\PeriodicEntry\Request\PeriodicEntrySearchRequest;
use Doctrine\ORM\EntityManagerInterface;

readonly class PeriodicEntryManager
{
    public function __construct(
        private PeriodicEntryRepository $repository,
        private EntityManagerInterface $entityManager,
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
    public function getPeriodicEntries(?PeriodicEntrySearchRequest $searchRequest = null): array
    {
        $searchRequest ??= new PeriodicEntrySearchRequest();

        /** @var PeriodicEntry[] $periodicEntries */
        $periodicEntries = $this->repository
            ->getPeriodicEntriesQueryBuilder($searchRequest)
            ->getQuery()
            ->getResult();

        return $periodicEntries;
    }
}
