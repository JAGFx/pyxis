<?php

namespace App\Domain\Entry\Message\Command\CreateOrUpdateEntry;

use App\Domain\Entry\Entity\Entry;
use App\Domain\Entry\Repository\EntryRepository;
use App\Shared\Cqs\Handler\CommandHandlerInterface;
use App\Shared\Cqs\Handler\EntityFinderTrait;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionException;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

/**
 * @see CreateOrUpdateEntryCommand
 */
readonly class CreateOrUpdateEntryHandler implements CommandHandlerInterface
{
    use EntityFinderTrait;

    public function __construct(
        private EntryRepository $repository,
        private EntityManagerInterface $entityManager,
        private ObjectMapperInterface $objectMapper,
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
            $entry = $this->findEntityByIntIdentifierOrFail(
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
