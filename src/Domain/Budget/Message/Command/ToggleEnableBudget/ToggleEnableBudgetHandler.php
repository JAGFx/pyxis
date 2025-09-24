<?php

namespace App\Domain\Budget\Message\Command\ToggleEnableBudget;

use App\Domain\Budget\Entity\Budget;
use App\Infrastructure\Doctrine\Exception\EntityNotFoundException;
use App\Infrastructure\Doctrine\Service\EntityFinder;
use App\Shared\Cqs\Handler\CommandHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionException;

/**
 * @see ToggleEnableBudgetCommand
 */
readonly class ToggleEnableBudgetHandler implements CommandHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EntityFinder $entityFinder,
    ) {
    }

    /**
     * @throws ReflectionException
     * @throws EntityNotFoundException
     */
    public function __invoke(ToggleEnableBudgetCommand $command): void
    {
        $budget = $this->entityFinder->findByIntIdentifierOrFail(
            Budget::class,
            $command->getOriginId(),
        );

        $budget->setEnabled(!$budget->isEnabled());

        $this->entityManager->flush();
    }
}
