<?php

namespace App\Domain\Assignment\Message\Command\CreateOrUpdateAssignment;

use App\Domain\Assignment\Entity\Assignment;
use App\Infrastructure\Doctrine\Service\EntityFinder;
use App\Shared\Cqs\Handler\CommandHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

/**
 * @see CreateOrUpdateAssignmentCommand
 */
readonly class CreateOrUpdateAssignmentHandler implements CommandHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ObjectMapperInterface $objectMapper,
        private EntityFinder $entityFinder,
    ) {
    }

    /**
     * @throws ReflectionException
     * @throws NotFoundHttpException
     */
    public function __invoke(CreateOrUpdateAssignmentCommand $command): void
    {
        if (null === $command->getOriginId()) {
            /** @var Assignment $assignment */
            $assignment = $this->objectMapper->map($command, Assignment::class);
            $this->entityManager->persist($assignment);
        } else {
            $entity = $this->entityFinder->findByIntIdentifierOrFail(
                Assignment::class,
                $command->getOriginId(),
            );

            $this->objectMapper->map($command, $entity);
        }

        $this->entityManager->flush();
    }
}
