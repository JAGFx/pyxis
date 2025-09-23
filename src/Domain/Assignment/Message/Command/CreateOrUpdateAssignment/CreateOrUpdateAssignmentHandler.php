<?php

namespace App\Domain\Assignment\Message\Command\CreateOrUpdateAssignment;

use App\Domain\Assignment\Entity\Assignment;
use App\Domain\Assignment\Repository\AssignmentRepository;
use App\Shared\Cqs\Handler\CommandHandlerInterface;
use App\Shared\Cqs\Handler\EntityFinderTrait;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionException;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

/**
 * @see CreateOrUpdateAssignmentCommand
 */
readonly class CreateOrUpdateAssignmentHandler implements CommandHandlerInterface
{
    use EntityFinderTrait;

    public function __construct(
        private AssignmentRepository $repository,
        private EntityManagerInterface $entityManager,
        private ObjectMapperInterface $objectMapper,
    ) {
    }

    /**
     * @throws ReflectionException
     */
    public function __invoke(CreateOrUpdateAssignmentCommand $command): void
    {
        if (null === $command->getOriginId()) {
            /** @var Assignment $assignment */
            $assignment = $this->objectMapper->map($command, Assignment::class);
            $this->repository->create($assignment);
        } else {
            $entity = $this->findEntityByIntIdentifierOrFail(
                Assignment::class,
                $command->getOriginId(),
            );

            $this->objectMapper->map($command, $entity);
        }

        $this->entityManager->flush();
    }
}
