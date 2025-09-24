<?php

namespace App\Domain\Entry\Message\Command\RemoveEntry;

use App\Domain\Entry\Entity\Entry;
use App\Domain\Entry\Repository\EntryRepository;
use App\Infrastructure\Doctrine\Service\EntityFinder;
use App\Shared\Cqs\Handler\CommandHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionException;

/**
 * @see RemoveEntryCommand
 */
readonly class RemoveEntryHandler implements CommandHandlerInterface
{
    public function __construct(
        private EntryRepository $repository,
        private EntityManagerInterface $entityManager,
        private EntityFinder $entityFinder,
    ) {
    }

    /**
     * @throws ReflectionException
     */
    public function __invoke(RemoveEntryCommand $command): void
    {
        $entry = $this->entityFinder->findByIntIdentifierOrFail(
            Entry::class,
            $command->getOriginId()
        );

        if (!$entry->isEditable()) {
            return;
        }

        $this->repository->remove($entry);

        $this->entityManager->flush();
    }
}
