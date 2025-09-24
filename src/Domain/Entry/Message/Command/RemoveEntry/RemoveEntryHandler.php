<?php

namespace App\Domain\Entry\Message\Command\RemoveEntry;

use App\Domain\Entry\Entity\Entry;
use App\Domain\Entry\Security\EntryVoter;
use App\Infrastructure\Doctrine\Service\EntityFinder;
use App\Shared\Cqs\Handler\CommandHandlerInterface;
use App\Shared\Security\AuthorizationChecker;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @see RemoveEntryCommand
 */
readonly class RemoveEntryHandler implements CommandHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EntityFinder $entityFinder,
        private AuthorizationChecker $authorizationChecker,
    ) {
    }

    /**
     * @throws ReflectionException
     * @throws NotFoundHttpException
     */
    public function __invoke(RemoveEntryCommand $command): void
    {
        $entry = $this->entityFinder->findByIntIdentifierOrFail(
            Entry::class,
            $command->getOriginId()
        );

        $this->authorizationChecker->denyAccessUnlessGranted(
            EntryVoter::MANAGE,
            $entry
        );

        $this->entityManager->remove($entry);

        $this->entityManager->flush();
    }
}
