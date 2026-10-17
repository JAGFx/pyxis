<?php

namespace App\Shared\Message\Command\ApplyBudgetAccountBalance;

use App\Domain\Entry\Entity\EntryFlagEnum;
use App\Domain\Entry\Message\Command\CreateOrUpdateEntry\CreateOrUpdateEntryCommand;
use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Shared\Cqs\Handler\CommandHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Throwable;

/**
 * @see ApplyBudgetAccountBalanceCommand
 */
readonly class ApplyBudgetAccountBalanceHandler implements CommandHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MessageBus $messageBus,
    ) {
    }

    /**
     * @throws Throwable
     * @throws ExceptionInterface
     */
    public function __invoke(ApplyBudgetAccountBalanceCommand $command): void
    {
        $budget   = $command->getBudget();
        $account  = $command->getAccount();
        $cashFlow = round($budget->getCashFlow($account), 2);

        if (0.0 === $cashFlow) {
            return;
        }

        $spentCommand = new CreateOrUpdateEntryCommand(
            account: $account,
            name: $budget->getName(),
            amount: $cashFlow,
            flags: [EntryFlagEnum::BALANCE],
        );

        $forecastCommand = new CreateOrUpdateEntryCommand(
            account: $account,
            name: $budget->getName(),
            amount: -$cashFlow,
            budget: $budget,
            flags: [EntryFlagEnum::BALANCE],
        );

        // TODO: Add flushable props
        $this->messageBus->dispatch($spentCommand);
        $this->messageBus->dispatch($forecastCommand);

        $this->entityManager->flush();
    }
}
