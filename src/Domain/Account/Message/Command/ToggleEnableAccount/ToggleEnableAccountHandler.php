<?php

namespace App\Domain\Account\Message\Command\ToggleEnableAccount;

use App\Domain\Account\Entity\Account;
use App\Domain\Account\Security\AccountVoter;
use App\Infrastructure\Doctrine\Service\EntityFinder;
use App\Shared\Cqs\Handler\CommandHandlerInterface;
use App\Shared\Security\AuthorizationChecker;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @see ToggleEnableAccountCommand
 */
readonly class ToggleEnableAccountHandler implements CommandHandlerInterface
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
    public function __invoke(ToggleEnableAccountCommand $command): void
    {
        $entity = $this->entityFinder->findByIntIdentifierOrFail(
            Account::class,
            $command->getOriginId()
        );

        if ($entity->isEnabled()) {
            $this->authorizationChecker->denyAccessUnlessGranted(AccountVoter::DISABLE, $entity);
        } else {
            $this->authorizationChecker->denyAccessUnlessGranted(AccountVoter::ENABLE, $entity);
        }

        $entity->setEnabled(!$entity->isEnabled());

        $this->entityManager->flush();
    }
}
