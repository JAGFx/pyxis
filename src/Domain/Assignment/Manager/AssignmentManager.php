<?php

namespace App\Domain\Assignment\Manager;

use App\Domain\Assignment\Entity\Assignment;
use App\Domain\Assignment\Model\AssignmentSearchCommand;
use App\Domain\Assignment\Repository\AssignmentRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class AssignmentManager
{
    public function __construct(
        private readonly AssignmentRepository $repository,
    ) {
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function balance(?AssignmentSearchCommand $command = null): float
    {
        /** @var ?float $data */
        $data = $this->repository
            ->balanceQueryBuilder($command ?? new AssignmentSearchCommand())
            ->getQuery()
            ->getSingleScalarResult();

        return $data ?? 0.0;
    }

    /**
     * @return Assignment[]
     */
    public function getAssignments(?AssignmentSearchCommand $command = null): array
    {
        /** @var Assignment[] $assignments */
        $assignments = $this->repository
            ->getAssignmentsQueryBuilder($command ?? new AssignmentSearchCommand())
            ->getQuery()
            ->getResult()
        ;

        return $assignments;
    }

    public function create(Assignment $assignment): void
    {
        $this->repository
            ->create($assignment)
            ->flush();
    }

    public function update(): void
    {
        $this->repository->flush();
    }

    public function remove(Assignment $assignment): void
    {
        $this->repository
            ->remove($assignment)
            ->flush();
    }
}
