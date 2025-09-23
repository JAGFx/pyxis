<?php

namespace App\Domain\PeriodicEntry\Manager;

use App\Domain\PeriodicEntry\Entity\PeriodicEntry;
use App\Domain\PeriodicEntry\Message\Command\CreateOrUpdatePeriodicEntryCommand;
use App\Domain\PeriodicEntry\Message\Command\RemovePeriodicEntryCommand;
use App\Domain\PeriodicEntry\Repository\PeriodicEntryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

readonly class PeriodicEntryManager
{
    public function __construct(
        private PeriodicEntryRepository $repository,
        private EntityManagerInterface $entityManager,
        private ObjectMapperInterface $objectMapper,
    ) {
    }

    public function create(CreateOrUpdatePeriodicEntryCommand $command, bool $flush = true): void
    {
        /** @var PeriodicEntry $periodicEntry */
        $periodicEntry = $this->objectMapper->map($command, PeriodicEntry::class);

        $this->repository->create($periodicEntry);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function update(CreateOrUpdatePeriodicEntryCommand $command, bool $flush = true): void
    {
        $this->objectMapper->map($command, $command->getOrigin());

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function remove(RemovePeriodicEntryCommand $command, bool $flush = true): void
    {
        $this->repository->remove($command->getPeriodicEntry());

        if ($flush) {
            $this->entityManager->flush();
        }
    }
}
