<?php

namespace App\Domain\Budget\Message\Command\ToggleEnableBudget;

use App\Domain\Budget\Entity\Budget;
use App\Domain\Budget\Security\BudgetVoter;
use App\Infrastructure\Cqs\Security\AuthorizationChecker;
use App\Infrastructure\Doctrine\Service\EntityFinder;
use App\Shared\Cqs\Handler\CommandHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @see ToggleEnableBudgetCommand
 */
readonly class ToggleEnableBudgetHandler implements CommandHandlerInterface
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
    public function __invoke(ToggleEnableBudgetCommand $command): void
    {
        $budget = $this->entityFinder->findByIntIdentifierOrFail(
            Budget::class,
            $command->getOriginId(),
        );

        $this->authorizationChecker->denyAccessUnlessGranted(
            BudgetVoter::MANAGE,
            $budget
        );

        $budget->setEnabled(!$budget->isEnabled());

        $this->entityManager->flush();
    }
}
