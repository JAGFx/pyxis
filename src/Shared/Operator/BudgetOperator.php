<?php

namespace App\Shared\Operator;

use App\Domain\Account\Manager\AccountManager;
use App\Domain\Budget\Entity\Budget;
use App\Domain\Budget\Entity\HistoryBudget;
use App\Domain\Budget\Manager\BudgetManager;
use App\Domain\Budget\Manager\HistoryBudgetManager;
use App\Domain\Budget\Message\Command\BudgetAccountBalanceCommand;
use App\Domain\Budget\Message\Query\BudgetSearchQuery;
use App\Domain\Budget\ValueObject\BudgetBalanceProgressValueObject;
use App\Domain\Budget\ValueObject\BudgetCashFlowByAccountValueObject;
use App\Domain\Budget\ValueObject\BudgetValueObject;
use App\Domain\Entry\Entity\EntryFlagEnum;
use App\Domain\Entry\Manager\EntryManager;
use App\Domain\Entry\Message\Command\EntryCreateOrUpdateCommand;
use App\Shared\Utils\YearRange;
use Doctrine\ORM\EntityManagerInterface;

readonly class BudgetOperator
{
    public function __construct(
        private BudgetManager $budgetManager,
        private HistoryBudgetManager $historyBudgetManager,
        private AccountManager $accountManager,
        private EntryManager $entryManager,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return BudgetBalanceProgressValueObject[]
     */
    public function getBudgetBalanceProgresses(BudgetSearchQuery $searchQuery): array
    {
        if (YearRange::current() === $searchQuery->getYear()) {
            $budgetsVO = $this->budgetManager->getBudgetValuesObject($searchQuery);

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

        $histories = $this->historyBudgetManager->getHistories($searchQuery);

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

    public function balancing(BudgetAccountBalanceCommand $budgetAccountBalance): void
    {
        $budget  = $budgetAccountBalance->getBudget();
        $account = $budgetAccountBalance->getAccount();

        if ($budget->hasPositiveCashFlow() || $budget->hasNegativeCashFlow()) {
            $spentCommand = new EntryCreateOrUpdateCommand(
                account: $account,
                name: $budget->getName(),
                amount: $budget->getCashFlow(),
                flags: [EntryFlagEnum::BALANCE],
            );

            $forecastCommand = new EntryCreateOrUpdateCommand(
                account: $account,
                name: $budget->getName(),
                amount: -$budget->getCashFlow(),
                budget: $budget,
                flags: [EntryFlagEnum::BALANCE],
            );

            $this->entryManager->create($spentCommand, false);
            $this->entryManager->create($forecastCommand, false);
            $this->entityManager->flush();
        }
    }
}
