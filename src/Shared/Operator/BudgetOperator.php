<?php

namespace App\Shared\Operator;

use App\Domain\Account\Manager\AccountManager;
use App\Domain\Budget\DTO\BudgetSearchCommand;
use App\Domain\Budget\Entity\Budget;
use App\Domain\Budget\Entity\HistoryBudget;
use App\Domain\Budget\Manager\BudgetManager;
use App\Domain\Budget\Manager\HistoryBudgetManager;
use App\Domain\Budget\ValueObject\BudgetBalanceProgressValueObject;
use App\Domain\Budget\ValueObject\BudgetCashFlowByAccountValueObject;
use App\Domain\Budget\ValueObject\BudgetValueObject;
use App\Shared\Utils\YearRange;

readonly class BudgetOperator
{
    public function __construct(
        private BudgetManager $budgetManager,
        private HistoryBudgetManager $historyBudgetManager,
        private AccountManager $accountManager,
    ) {
    }

    /**
     * @return BudgetBalanceProgressValueObject[]
     */
    public function getBudgetBalanceProgresses(BudgetSearchCommand $command): array
    {
        // TODO: Add test for this
        if (YearRange::current() === $command->getYear()) {
            $budgetsVO = $this->budgetManager->getBudgetValuesObject($command);

            return array_map(
                fn (BudgetValueObject $budgetValueObject): BudgetBalanceProgressValueObject => new BudgetBalanceProgressValueObject(
                    $budgetValueObject->getName(),
                    $budgetValueObject->getProgress(true),
                    $budgetValueObject->getStatus(true),
                    $budgetValueObject->getAmount(),
                    $budgetValueObject->getRelativeProgress(true)
                ),
                $budgetsVO
            );
        }

        $histories = $this->historyBudgetManager->getHistories($command);

        return array_map(
            fn (HistoryBudget $history): BudgetBalanceProgressValueObject => new BudgetBalanceProgressValueObject(
                $history->getBudget()?->getName(),
                $history->getSpent() ?? 0.0,
                $history->getStatus(),
                $history->getAmount(),
                $history->getRelativeProgress()
            ),
            $histories
        );
    }

    /**
     * @return BudgetCashFlowByAccountValueObject[]
     */
    public function getBudgetCashFlowsByAccount(Budget $budget): array
    {
        $accounts = $this->accountManager->getAccounts();

        $cashFlows = [];
        foreach ($accounts as $account) {
            $cashFlow = $budget->getCashFlow($account);

            if (0.0 === $cashFlow) {
                continue;
            }

            $cashFlows[] = new BudgetCashFlowByAccountValueObject(
                $budget,
                $account,
                $cashFlow
            );
        }

        return $cashFlows;
    }
}
