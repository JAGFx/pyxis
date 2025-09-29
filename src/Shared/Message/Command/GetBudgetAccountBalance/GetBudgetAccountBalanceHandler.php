<?php

namespace App\Shared\Message\Command\GetBudgetAccountBalance;

use App\Domain\Entry\Entity\EntryFlagEnum;
use App\Domain\Entry\Message\Command\CreateOrUpdateEntry\CreateOrUpdateEntryCommand;
use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Shared\Cqs\Handler\CommandHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Throwable;

/**
 * @see GetBudgetAccountBalanceCommand
 */
readonly class GetBudgetAccountBalanceHandler implements CommandHandlerInterface
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
    public function __invoke(GetBudgetAccountBalanceCommand $command): void
    {
        $budget  = $command->getBudget();
        $account = $command->getAccount();

        if ($budget->hasPositiveCashFlow() || $budget->hasNegativeCashFlow()) {
            $spentCommand = new CreateOrUpdateEntryCommand(
                account: $account,
                name: $budget->getName(),
                amount: $budget->getCashFlow(),
                flags: [EntryFlagEnum::BALANCE],
            );

            $forecastCommand = new CreateOrUpdateEntryCommand(
                account: $account,
                name: $budget->getName(),
                amount: -$budget->getCashFlow(),
                budget: $budget,
                flags: [EntryFlagEnum::BALANCE],
            );

            // TODO: Add flushable props
            $this->messageBus->dispatch($spentCommand);
            $this->messageBus->dispatch($forecastCommand);

            $this->entityManager->flush();
        }
    }
}
