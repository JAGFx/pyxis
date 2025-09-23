<?php

namespace App\Domain\PeriodicEntry\Message\Query\FindPeriodicEntries;

use App\Domain\PeriodicEntry\Entity\PeriodicEntry;
use App\Domain\PeriodicEntry\Repository\PeriodicEntryRepository;
use App\Shared\Cqs\Handler\QueryHandlerInterface;

/**
 * @see FindPeriodicEntriesQuery
 */
readonly class FindPeriodicEntriesHandler implements QueryHandlerInterface
{
    public function __construct(
        private PeriodicEntryRepository $repository,
    ) {
    }

    /** @return PeriodicEntry[] */
    public function __invoke(FindPeriodicEntriesQuery $query): array
    {
        /** @var PeriodicEntry[] $periodicEntries */
        $periodicEntries = $this->repository
            ->getPeriodicEntriesQueryBuilder($query)
            ->getQuery()
            ->getResult();

        return $periodicEntries;
    }
}
