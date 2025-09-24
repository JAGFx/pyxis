<?php

namespace App\Domain\Account\Message\Command\ToggleEnableAccount;

use App\Domain\Account\Entity\Account;
use App\Infrastructure\Doctrine\Service\EntityFinder;
use App\Shared\Cqs\Handler\CommandHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionException;

/**
 * @see ToggleEnableAccountCommand
 */
readonly class ToggleEnableAccountHandler implements CommandHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EntityFinder $entityFinder,
    ) {
    }

    /**
     * @throws ReflectionException
     */
    public function __invoke(ToggleEnableAccountCommand $command): void
    {
        $entity = $this->entityFinder->findByIntIdentifierOrFail(
            Account::class,
            $command->getOriginId()
        );

        $entity->setEnabled(!$entity->isEnabled());

        $this->entityManager->flush();
    }
}
