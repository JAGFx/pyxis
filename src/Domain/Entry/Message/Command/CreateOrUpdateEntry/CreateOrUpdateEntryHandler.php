<?php

namespace App\Domain\Entry\Message\Command\CreateOrUpdateEntry;

use App\Domain\Entry\Entity\Entry;
use App\Domain\Entry\Security\EntryVoter;
use App\Infrastructure\Cqs\Security\AuthorizationChecker;
use App\Infrastructure\Doctrine\Service\EntityFinder;
use App\Shared\Cqs\Handler\CommandHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

/**
 * @see CreateOrUpdateEntryCommand
 */
readonly class CreateOrUpdateEntryHandler implements CommandHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ObjectMapperInterface $objectMapper,
        private EntityFinder $entityFinder,
        private AuthorizationChecker $authorizationChecker,
    ) {
    }

    /**
     * @throws ReflectionException
     * @throws NotFoundHttpException
     */
    public function __invoke(CreateOrUpdateEntryCommand $command): void
    {
        if (null === $command->getOriginId()) {
            /** @var Entry $entry */
            $entry = $this->objectMapper->map($command, Entry::class);

            $this->entityManager->persist($entry);
        } else {
            /** @var Entry $entry */
            $entry = $this->entityFinder->findByIntIdentifierOrFail(
                Entry::class,
                $command->getOriginId()
            );

            $this->authorizationChecker->denyAccessUnlessGranted(
                EntryVoter::MANAGE,
                $entry
            );

            $this->objectMapper->map($command, $entry);
        }

        $this->entityManager->flush();
    }
}
