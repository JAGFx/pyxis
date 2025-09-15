<?php

namespace App\Shared\Operator;

use App\Domain\Budget\Entity\HistoryBudget;
use App\Domain\Budget\Manager\BudgetManager;
use App\Domain\Budget\Manager\HistoryBudgetManager;
use App\Domain\Budget\Request\BudgetSearchRequest;
use App\Domain\Budget\Request\HistoryBudgetSearchRequest;
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
            new BudgetSearchRequest()
                ->setShowCredits(false)
                ->setYear($year)
        );

        foreach ($budgetsValues as $budgetValue) {
            $budget = $this->budgetManager->find($budgetValue->getId());

            if (is_null($budget)) {
                continue;
            }

            $historyBudgets = $this->historyBudgetManager->getHistories(
                new HistoryBudgetSearchRequest(
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

            $historyBudget = new HistoryBudget()
                ->setBudget($budget)
                ->setAmount($budget->getAmount())
                ->setDate(YearRange::firstDayOf($year))
                ->setSpent($budgetValue->getProgress(true))
                ->setRelativeProgress($relativeProgress)
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
