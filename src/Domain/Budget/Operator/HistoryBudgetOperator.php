<?php

namespace App\Domain\Budget\Operator;

use App\Domain\Budget\DTO\BudgetSearchCommand;
use App\Domain\Budget\DTO\HistoryBudgetSearchCommand;
use App\Domain\Budget\Entity\HistoryBudget;
use App\Domain\Budget\Manager\BudgetManager;
use App\Domain\Budget\Manager\HistoryBudgetManager;
use App\Shared\Utils\YearRange;
use Psr\Log\LoggerInterface;
use Throwable;

class HistoryBudgetOperator
{
    public function __construct(
        private readonly BudgetManager $budgetManager,
        private readonly HistoryBudgetManager $historyBudgetManager,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function generateHistoryBudgetsForYear(int $year): void
    {
        $budgetsValues = $this->budgetManager->getBudgetValuesObject(
            new BudgetSearchCommand()
               ->setShowCredits(false)
               ->setYear($year)
        );

        foreach ($budgetsValues as $budgetValue) {
            $budget = $this->budgetManager->find($budgetValue->getId());

            if (is_null($budget)) {
                continue;
            }

            $historyBudgets = $this->historyBudgetManager->getHistories(
                new HistoryBudgetSearchCommand(
                    $budget,
                    $year
                )
            );

            if ([] !== $historyBudgets) {
                continue;
            }

            $historyBudget = new HistoryBudget()
                ->setBudget($budget)
                ->setAmount($budget->getAmount())
                ->setDate(YearRange::firstDayOf($year))
                ->setRelativeProgress($budgetValue->getProgress(true))
            ;

            try {
                $this->historyBudgetManager->create($historyBudget);
            } catch (Throwable $throwable) {
                $this->logger->error($throwable->getMessage(), [
                    'budget_id' => $budget->getId(),
                    'year'      => $year,
                    'exception' => $throwable,
                ]);
            }
        }
    }
}
