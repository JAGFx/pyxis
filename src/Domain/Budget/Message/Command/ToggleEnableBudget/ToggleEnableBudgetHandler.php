<?php

namespace App\Domain\Budget\Message\Command\ToggleEnableBudget;

use App\Domain\Budget\Entity\Budget;
use App\Shared\Cqs\Handler\CommandHandlerInterface;
use App\Shared\Cqs\Handler\EntityFinderTrait;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionException;

/**
 * @see ToggleEnableBudgetCommand
 */
readonly class ToggleEnableBudgetHandler implements CommandHandlerInterface
{
    use EntityFinderTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws ReflectionException
     */
    public function __invoke(ToggleEnableBudgetCommand $command): void
    {
        $budget = $this->findEntityByIntIdentifierOrFail(
            Budget::class,
            $command->getOriginId(),
        );

        $budget->setEnabled(!$budget->isEnabled());

        $this->entityManager->flush();
    }
}
