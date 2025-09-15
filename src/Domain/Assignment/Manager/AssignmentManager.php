<?php

namespace App\Domain\Assignment\Manager;

use App\Domain\Assignment\DTO\AssignmentSearchCommand;
use App\Domain\Assignment\Entity\Assignment;
use App\Domain\Assignment\Repository\AssignmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class AssignmentManager
{
    public function __construct(
        private readonly AssignmentRepository $repository,
        private readonly EntityManagerInterface $entityManager,
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

    public function create(Assignment $entity, bool $flush = true): void
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

    public function remove(Assignment $entity, bool $flush = true): void
    {
        $this->repository->remove($entity);

        if ($flush) {
            $this->entityManager->flush();
        }
    }
}
