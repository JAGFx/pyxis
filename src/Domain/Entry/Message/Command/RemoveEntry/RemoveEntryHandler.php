<?php

namespace App\Domain\Entry\Message\Command\RemoveEntry;

use App\Domain\Entry\Entity\Entry;
use App\Domain\Entry\Repository\EntryRepository;
use App\Shared\Cqs\Handler\CommandHandlerInterface;
use App\Shared\Cqs\Handler\EntityFinderTrait;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionException;

/**
 * @see RemoveEntryCommand
 */
readonly class RemoveEntryHandler implements CommandHandlerInterface
{
    use EntityFinderTrait;

    public function __construct(
        private EntryRepository $repository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws ReflectionException
     */
    public function __invoke(RemoveEntryCommand $command): void
    {
        $entry = $this->findEntityByIntIdentifierOrFail(
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
