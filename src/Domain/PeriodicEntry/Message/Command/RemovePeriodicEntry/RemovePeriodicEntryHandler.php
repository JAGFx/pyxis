<?php

namespace App\Domain\PeriodicEntry\Message\Command\RemovePeriodicEntry;

use App\Domain\PeriodicEntry\Entity\PeriodicEntry;
use App\Infrastructure\Doctrine\Exception\EntityNotFoundException;
use App\Infrastructure\Doctrine\Service\EntityFinder;
use App\Shared\Cqs\Handler\CommandHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionException;

/**
 * @see RemovePeriodicEntryCommand
 */
readonly class RemovePeriodicEntryHandler implements CommandHandlerInterface
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
    public function __invoke(RemovePeriodicEntryCommand $command): void
    {
        $entity = $this->entityFinder->findByIntIdentifierOrFail(
            PeriodicEntry::class,
            $command->getOriginId(),
        );

        $this->entityManager->remove($entity);

        $this->entityManager->flush();
    }
}
