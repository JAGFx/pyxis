<?php

namespace App\Domain\Account\Message\Command\ToggleEnableAccount;

use App\Domain\Account\Entity\Account;
use App\Shared\Cqs\Handler\CommandHandlerInterface;
use App\Shared\Cqs\Handler\EntityFinderTrait;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionException;

/**
 * @see ToggleEnableAccountCommand
 */
readonly class ToggleEnableAccountHandler implements CommandHandlerInterface
{
    use EntityFinderTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws ReflectionException
     */
    public function __invoke(ToggleEnableAccountCommand $command): void
    {
        $entity = $this->findEntityByIntIdentifierOrFail(
            Account::class,
            $command->getOriginId()
        );

        $entity->setEnabled(!$entity->isEnabled());

        $this->entityManager->flush();
    }
}
