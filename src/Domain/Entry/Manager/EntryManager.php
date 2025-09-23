<?php

namespace App\Domain\Entry\Manager;

use App\Domain\Entry\Entity\Entry;
use App\Domain\Entry\Message\Command\CreateOrUpdateEntryCommand;
use App\Domain\Entry\Message\Command\RemoveEntryCommand;
use App\Domain\Entry\Repository\EntryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

readonly class EntryManager
{
    public function __construct(
        private EntryRepository $repository,
        private EntityManagerInterface $entityManager,
        private ObjectMapperInterface $objectMapper,
    ) {
    }

    public function create(CreateOrUpdateEntryCommand $command, bool $flush = true): void
    {
        /** @var Entry $entry */
        $entry = $this->objectMapper->map($command, Entry::class);

        $this->repository->create($entry);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function update(CreateOrUpdateEntryCommand $command, bool $flush = true): void
    {
        /** @var Entry $entry */
        $entry = $this->objectMapper->map($command, $command->getOrigin());

        if ($entry->isEditable()) {
            return; // TODO: Throw exception instead
        }

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function remove(RemoveEntryCommand $command, bool $flush = true): void
    {
        $entry = $command->getEntry();

        if ($entry->isEditable()) {
            return;
        }

        $this->repository->remove($entry);
        if ($flush) {
            $this->entityManager->flush();
        }
    }
}
