<?php

namespace App\Domain\Entry\Message\Command\RemoveEntry;

use App\Domain\Entry\Entity\Entry;
use App\Infrastructure\Doctrine\Exception\EntityNotFoundException;
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
        private EntityManagerInterface $entityManager,
        private EntityFinder $entityFinder,
    ) {
    }

    /**
     * @throws ReflectionException
     * @throws EntityNotFoundException
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

        $this->entityManager->remove($entry);

        $this->entityManager->flush();
    }
}
