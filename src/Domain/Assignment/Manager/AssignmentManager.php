<?php

namespace App\Domain\Assignment\Manager;

use App\Domain\Assignment\Entity\Assignment;
use App\Domain\Assignment\Message\Command\AssigmentRemoveCommand;
use App\Domain\Assignment\Message\Command\AssignmentCreateOrUpdateCommand;
use App\Domain\Assignment\Message\Query\AssignmentSearchQuery;
use App\Domain\Assignment\Repository\AssignmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

readonly class AssignmentManager
{
    public function __construct(
        private AssignmentRepository $repository,
        private EntityManagerInterface $entityManager,
        private ObjectMapperInterface $objectMapper,
    ) {
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function balance(?AssignmentSearchQuery $searchQuery = null): float
    {
        /** @var ?float $data */
        $data = $this->repository
            ->balanceQueryBuilder($searchQuery ?? new AssignmentSearchQuery())
            ->getQuery()
            ->getSingleScalarResult();

        return $data ?? 0.0;
    }

    /**
     * @return Assignment[]
     */
    public function getAssignments(?AssignmentSearchQuery $searchQuery = null): array
    {
        /** @var Assignment[] $assignments */
        $assignments = $this->repository
            ->getAssignmentsQueryBuilder($searchQuery ?? new AssignmentSearchQuery())
            ->getQuery()
            ->getResult()
        ;

        return $assignments;
    }

    public function create(AssignmentCreateOrUpdateCommand $command, bool $flush = true): void
    {
        /** @var Assignment $assignment */
        $assignment = $this->objectMapper->map($command, Assignment::class);

        $this->repository->create($assignment);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function update(AssignmentCreateOrUpdateCommand $command, bool $flush = true): void
    {
        $this->objectMapper->map($command, $command->getOrigin());

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function remove(AssigmentRemoveCommand $command, bool $flush = true): void
    {
        /** @var Assignment $assigment */
        $assigment = $command->getAssignment();

        $this->repository->remove($assigment);

        if ($flush) {
            $this->entityManager->flush();
        }
    }
}
