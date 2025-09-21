<?php

namespace App\Shared\Operator;

use App\Domain\Budget\Manager\BudgetManager;
use App\Domain\Budget\Manager\HistoryBudgetManager;
use App\Domain\Budget\Message\Command\HistoryCreateCommand;
use App\Domain\Budget\Message\Query\BudgetSearchQuery;
use App\Domain\Budget\Message\Query\HistoryBudgetSearchQuery;
use App\Shared\Utils\YearRange;
use Psr\Log\LoggerInterface;
use Throwable;

readonly class HistoryBudgetOperator
{
    public function __construct(
        private BudgetManager $budgetManager,
        private HistoryBudgetManager $historyBudgetManager,
        private LoggerInterface $logger,
    ) {
    }

    public function generateHistoryBudgetsForYear(int $year): void
    {
        $budgetsValues = $this->budgetManager->getBudgetValuesObject(
            new BudgetSearchQuery()
                ->setShowCredits(false)
                ->setYear($year)
        );

        foreach ($budgetsValues as $budgetValue) {
            $budget = $this->budgetManager->find($budgetValue->getId());

            if (is_null($budget)) {
                continue;
            }

            $historyBudgets = $this->historyBudgetManager->getHistories(
                new HistoryBudgetSearchQuery(
                    $budget,
                    $year
                )
            );

            if ([] !== $historyBudgets) {
                continue;
            }

            $relativeProgress = 0.0 !== $budget->getAmount()
                ? ($budgetValue->getProgress(true) / $budget->getAmount()) * 100
                : 0.0;

            $createCommand = new HistoryCreateCommand(
                $budget,
                $budget->getAmount(),
                YearRange::firstDayOf($year),
                $budgetValue->getProgress(true),
                $relativeProgress,
            );

            try {
                $this->historyBudgetManager->create($createCommand);
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
