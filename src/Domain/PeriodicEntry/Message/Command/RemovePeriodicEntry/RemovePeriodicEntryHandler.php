<?php

namespace App\Domain\PeriodicEntry\Message\Command\RemovePeriodicEntry;

use App\Domain\PeriodicEntry\Entity\PeriodicEntry;
use App\Domain\PeriodicEntry\Repository\PeriodicEntryRepository;
use App\Shared\Cqs\Handler\CommandHandlerInterface;
use App\Shared\Cqs\Handler\EntityFinderTrait;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionException;

/**
 * @see RemovePeriodicEntryCommand
 */
readonly class RemovePeriodicEntryHandler implements CommandHandlerInterface
{
    use EntityFinderTrait;

    public function __construct(
        private PeriodicEntryRepository $repository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws ReflectionException
     */
    public function __invoke(RemovePeriodicEntryCommand $command): void
    {
        $entity = $this->findEntityByIntIdentifierOrFail(
            PeriodicEntry::class,
            $command->getOriginId(),
        );

        $this->repository->remove($entity);

        $this->entityManager->flush();
    }
}
