<?php

namespace App\Domain\Assignment\Manager;

use App\Domain\Assignment\Entity\Assignment;
use App\Domain\Assignment\Message\Command\CreateOrUpdateAssignmentCommand;
use App\Domain\Assignment\Message\Command\RemoveAssignmentCommand;
use App\Domain\Assignment\Repository\AssignmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

readonly class AssignmentManager
{
    public function __construct(
        private AssignmentRepository $repository,
        private EntityManagerInterface $entityManager,
        private ObjectMapperInterface $objectMapper,
    ) {
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
