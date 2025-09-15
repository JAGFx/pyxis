<?php

namespace App\Domain\Assignment\Manager;

use App\Domain\Assignment\Entity\Assignment;
use App\Domain\Assignment\Repository\AssignmentRepository;
use App\Domain\Assignment\Request\AssignmentSearchRequest;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

readonly class AssignmentManager
{
    public function __construct(
        private AssignmentRepository $repository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function balance(?AssignmentSearchRequest $searchRequest = null): float
    {
        /** @var ?float $data */
        $data = $this->repository
            ->balanceQueryBuilder($searchRequest ?? new AssignmentSearchRequest())
            ->getQuery()
            ->getSingleScalarResult();

        return $data ?? 0.0;
    }

    /**
     * @return Assignment[]
     */
    public function getAssignments(?AssignmentSearchRequest $searchRequest = null): array
    {
        /** @var Assignment[] $assignments */
        $assignments = $this->repository
            ->getAssignmentsQueryBuilder($searchRequest ?? new AssignmentSearchRequest())
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
