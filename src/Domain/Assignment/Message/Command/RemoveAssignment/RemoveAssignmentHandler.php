<?php

namespace App\Domain\Assignment\Message\Command\RemoveAssignment;

use App\Domain\Assignment\Entity\Assignment;
use App\Domain\Assignment\Repository\AssignmentRepository;
use App\Shared\Cqs\Handler\CommandHandlerInterface;
use App\Shared\Cqs\Handler\EntityFinderTrait;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionException;

/**
 * @see RemoveAssignmentCommand
 */
class RemoveAssignmentHandler implements CommandHandlerInterface
{
    use EntityFinderTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AssignmentRepository $repository,
    ) {
    }

    /**
     * @throws ReflectionException
     */
    public function __invoke(RemoveAssignmentCommand $command): void
    {
        $assigment = $this->findEntityByIntIdentifierOrFail(
            Assignment::class,
            $command->getAssignmentId()
        );

        $this->repository->remove($assigment);

        $this->entityManager->flush();
    }
}
