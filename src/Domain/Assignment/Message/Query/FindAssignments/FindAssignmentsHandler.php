<?php

namespace App\Domain\Assignment\Message\Query\FindAssignments;

use App\Domain\Assignment\Entity\Assignment;
use App\Domain\Assignment\Repository\AssignmentRepository;
use App\Shared\Cqs\Handler\QueryHandlerInterface;

/**
 * @see FindAssignmentsQuery
 */
readonly class FindAssignmentsHandler implements QueryHandlerInterface
{
    public function __construct(
        private AssignmentRepository $repository,
    ) {
    }

    /**
     * @return Assignment[]
     */
    public function __invoke(FindAssignmentsQuery $query): array
    {
        /** @var Assignment[] $assignments */
        $assignments = $this->repository
            ->getAssignmentsQueryBuilder($query)
            ->getQuery()
            ->getResult()
        ;

        return $assignments;
    }
}
