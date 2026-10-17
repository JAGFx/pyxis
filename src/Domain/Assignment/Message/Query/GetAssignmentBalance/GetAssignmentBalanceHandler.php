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
    public function __invoke(GetAssignmentBalanceQuery $query): int
    {
        /** @var int|string|null $startingAmounts */
        $startingAmounts = $this->repository
            ->balanceQueryBuilder($query)
            ->getQuery()
            ->getSingleScalarResult();

        /** @var int|string|null $linkedEntries */
        $linkedEntries = $this->repository
            ->linkedEntriesQueryBuilder($query)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $startingAmounts + (int) $linkedEntries;
    }
}
