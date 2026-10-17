<?php

namespace App\Shared\Message\Command\GenerateHistoryBudgetsForYear;

use App\Domain\Budget\Entity\Budget;
use App\Domain\Budget\Message\Command\CreateHistoryBudget\CreateHistoryBudgetCommand;
use App\Domain\Budget\Message\Query\FindBudgetVO\FindBudgetVOQuery;
use App\Domain\Budget\Message\Query\FindHistoryBudgets\FindHistoryBudgetsQuery;
use App\Domain\Budget\ValueObject\BudgetValueObject;
use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Infrastructure\Doctrine\Service\EntityFinder;
use App\Shared\Cqs\Handler\CommandHandlerInterface;
use App\Shared\Utils\YearRange;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Throwable;

/**
 * @see GenerateHistoryBudgetsForYearCommand
 */
readonly class GenerateHistoryBudgetsForYearHandler implements CommandHandlerInterface
{
    public function __construct(
        private MessageBus $messageBus,
        private EntityFinder $entityFinder,
    ) {
    }

    /**
     * @throws Throwable
     * @throws ExceptionInterface
     */
    public function __invoke(GenerateHistoryBudgetsForYearCommand $command): void
    {
        $year = $command->getYear();

        /** @var BudgetValueObject[] $budgetsValues */
        $budgetsValues = $this->messageBus->dispatch(
            new FindBudgetVOQuery()
                ->setShowCredits(false)
                ->setYear($year)
        );

        foreach ($budgetsValues as $budgetValue) {
            $budget = $this->entityFinder->findByIntIdentifier(
                Budget::class,
                $budgetValue->getId()
            );

            if (is_null($budget)) {
                continue;
            }

            $historyBudgets = $this->messageBus->dispatch(new FindHistoryBudgetsQuery(
                $budget->getId(),
                $year
            ));

            if ([] !== $historyBudgets) {
                continue;
            }

            $relativeProgress = 0 !== $budget->getAmount()
                ? ($budgetValue->getProgress(true) / $budget->getAmount()) * 100
                : 0.0;

            $createCommand = new CreateHistoryBudgetCommand(
                $budget,
                $budget->getAmount(),
                YearRange::firstDayOf($year),
                $budgetValue->getProgress(true),
                $relativeProgress,
            );

            // No per budget catch: a failure may close the entity manager, so the whole year fails and the
            // transaction rolls back. Rerunning is safe as budgets already historized are skipped.
            $this->messageBus->dispatch($createCommand);
        }
    }
}
