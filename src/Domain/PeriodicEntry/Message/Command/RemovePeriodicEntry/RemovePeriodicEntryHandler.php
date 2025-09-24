<?php

namespace App\Domain\PeriodicEntry\Message\Command\RemovePeriodicEntry;

use App\Domain\PeriodicEntry\Entity\PeriodicEntry;
use App\Domain\PeriodicEntry\Repository\PeriodicEntryRepository;
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
        private PeriodicEntryRepository $repository,
        private EntityManagerInterface $entityManager,
        private EntityFinder $entityFinder,
    ) {
    }

    /**
     * @throws ReflectionException
     */
    public function __invoke(RemovePeriodicEntryCommand $command): void
    {
        $entity = $this->entityFinder->findByIntIdentifierOrFail(
            PeriodicEntry::class,
            $command->getOriginId(),
        );

        $this->repository->remove($entity);

        $this->entityManager->flush();
    }
}
