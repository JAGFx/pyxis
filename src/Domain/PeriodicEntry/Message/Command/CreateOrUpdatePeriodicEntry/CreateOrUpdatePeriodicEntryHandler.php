<?php

namespace App\Domain\PeriodicEntry\Message\Command\CreateOrUpdatePeriodicEntry;

use App\Domain\PeriodicEntry\Entity\PeriodicEntry;
use App\Domain\PeriodicEntry\Repository\PeriodicEntryRepository;
use App\Shared\Cqs\Handler\CommandHandlerInterface;
use App\Shared\Cqs\Handler\EntityFinderTrait;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionException;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

/**
 * @see CreateOrUpdatePeriodicEntryCommand
 */
readonly class CreateOrUpdatePeriodicEntryHandler implements CommandHandlerInterface
{
    use EntityFinderTrait;

    public function __construct(
        private PeriodicEntryRepository $repository,
        private EntityManagerInterface $entityManager,
        private ObjectMapperInterface $objectMapper,
    ) {
    }

    /**
     * @throws ReflectionException
     */
    public function __invoke(CreateOrUpdatePeriodicEntryCommand $command): void
    {
        if (null === $command->getOriginId()) {
            /** @var PeriodicEntry $periodicEntry */
            $periodicEntry = $this->objectMapper->map($command, PeriodicEntry::class);

            $this->repository->create($periodicEntry);
        } else {
            $entity = $this->findEntityByIntIdentifierOrFail(
                PeriodicEntry::class,
                $command->getOriginId(),
            );

            $this->objectMapper->map($command, $entity);
        }

        $this->entityManager->flush();
    }
}
