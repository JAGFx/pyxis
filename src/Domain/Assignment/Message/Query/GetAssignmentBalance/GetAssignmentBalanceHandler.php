<?php

namespace App\Domain\Assignment\Message\Query\GetAssignmentBalance;

use App\Domain\Assignment\Repository\AssignmentRepository;
use App\Shared\Cqs\Handler\QueryHandlerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

/**
 * @see GetAssignmentBalanceQuery
 */
readonly class GetAssignmentBalanceHandler implements QueryHandlerInterface
{
    public function __construct(
        private AssignmentRepository $repository,
    ) {
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function __invoke(GetAssignmentBalanceQuery $query): float
    {
        /** @var ?float $startingAmounts */
        $startingAmounts = $this->repository
            ->balanceQueryBuilder($query)
            ->getQuery()
            ->getSingleScalarResult();

        /** @var ?float $linkedEntries */
        $linkedEntries = $this->repository
            ->linkedEntriesQueryBuilder($query)
            ->getQuery()
            ->getSingleScalarResult();

        return round((float) $startingAmounts + (float) $linkedEntries, 2);
    }
}
