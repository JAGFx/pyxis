<?php

namespace App\Domain\Assignment\Message\Command\RemoveAssignment;

use App\Domain\Assignment\Entity\Assignment;
use App\Infrastructure\Doctrine\Exception\EntityNotFoundException;
use App\Infrastructure\Doctrine\Service\EntityFinder;
use App\Shared\Cqs\Handler\CommandHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionException;

/**
 * @see RemoveAssignmentCommand
 */
readonly class RemoveAssignmentHandler implements CommandHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EntityFinder $entityFinder,
    ) {
    }

    /**
     * @throws ReflectionException
     * @throws EntityNotFoundException
     */
    public function __invoke(RemoveAssignmentCommand $command): void
    {
        $assigment = $this->entityFinder->findByIntIdentifierOrFail(
            Assignment::class,
            $command->getAssignmentId()
        );

        $this->entityManager->remove($assigment);

        $this->entityManager->flush();
    }
}
