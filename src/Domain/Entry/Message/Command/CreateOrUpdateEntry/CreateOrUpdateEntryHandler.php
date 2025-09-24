<?php

namespace App\Domain\Entry\Message\Command\CreateOrUpdateEntry;

use App\Domain\Entry\Entity\Entry;
use App\Domain\Entry\Repository\EntryRepository;
use App\Infrastructure\Doctrine\Service\EntityFinder;
use App\Shared\Cqs\Handler\CommandHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionException;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

/**
 * @see CreateOrUpdateEntryCommand
 */
readonly class CreateOrUpdateEntryHandler implements CommandHandlerInterface
{
    public function __construct(
        private EntryRepository $repository,
        private EntityManagerInterface $entityManager,
        private ObjectMapperInterface $objectMapper,
        private EntityFinder $entityFinder,
    ) {
    }

    /**
     * @throws ReflectionException
     */
    public function __invoke(CreateOrUpdateEntryCommand $command): void
    {
        if (null === $command->getOriginId()) {
            /** @var Entry $entry */
            $entry = $this->objectMapper->map($command, Entry::class);

            // TODO: Use persist from EM and keep repository only fod DB queries
            $this->repository->create($entry);
        } else {
            /** @var Entry $entry */
            $entry = $this->entityFinder->findByIntIdentifierOrFail(
                Entry::class,
                $command->getOriginId()
            );

            if (!$entry->isEditable()) {
                return; // TODO: Throw exception instead
            }

            $this->objectMapper->map($command, $entry);
        }

        $this->entityManager->flush();
    }
}
