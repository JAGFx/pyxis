<?php

namespace App\Domain\Assignment\Manager;

use App\Domain\Assignment\Entity\Assignment;
use App\Domain\Assignment\Message\Command\CreateOrUpdateAssignmentCommand;
use App\Domain\Assignment\Message\Command\RemoveAssignmentCommand;
use App\Domain\Assignment\Message\Query\FindAssignmentsQuery;
use App\Domain\Assignment\Message\Query\GetAssignmentBalanceQuery;
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
    public function balance(GetAssignmentBalanceQuery $query): float
    {
        /** @var ?float $data */
        $data = $this->repository
            ->balanceQueryBuilder($query)
            ->getQuery()
            ->getSingleScalarResult();

        return $data ?? 0.0;
    }

    /**
     * @return Assignment[]
     */
    public function getAssignments(?FindAssignmentsQuery $searchQuery = null): array
    {
        /** @var Assignment[] $assignments */
        $assignments = $this->repository
            ->getAssignmentsQueryBuilder($searchQuery ?? new FindAssignmentsQuery())
            ->getQuery()
            ->getResult()
        ;

        return $assignments;
    }

    public function create(CreateOrUpdateAssignmentCommand $command, bool $flush = true): void
    {
        /** @var Assignment $assignment */
        $assignment = $this->objectMapper->map($command, Assignment::class);

        $this->repository->create($assignment);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function update(CreateOrUpdateAssignmentCommand $command, bool $flush = true): void
    {
        $this->objectMapper->map($command, $command->getOrigin());

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function remove(RemoveAssignmentCommand $command, bool $flush = true): void
    {
        /** @var Assignment $assigment */
        $assigment = $command->getAssignment();

        $this->repository->remove($assigment);

        if ($flush) {
            $this->entityManager->flush();
        }
    }
}
