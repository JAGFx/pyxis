<?php

namespace App\Shared\Operator;

use App\Domain\Account\Manager\AccountManager;
use App\Domain\Budget\Entity\Budget;
use App\Domain\Budget\Entity\HistoryBudget;
use App\Domain\Budget\Manager\BudgetManager;
use App\Domain\Budget\Manager\HistoryBudgetManager;
use App\Domain\Budget\Request\BudgetAccountBalanceRequest;
use App\Domain\Budget\Request\BudgetSearchRequest;
use App\Domain\Budget\ValueObject\BudgetBalanceProgressValueObject;
use App\Domain\Budget\ValueObject\BudgetCashFlowByAccountValueObject;
use App\Domain\Budget\ValueObject\BudgetValueObject;
use App\Domain\Entry\Entity\Entry;
use App\Domain\Entry\Entity\EntryFlagEnum;
use App\Domain\Entry\Manager\EntryManager;
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
    public function getBudgetBalanceProgresses(BudgetSearchRequest $searchRequest): array
    {
        if (YearRange::current() === $searchRequest->getYear()) {
            $budgetsVO = $this->budgetManager->getBudgetValuesObject($searchRequest);

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

        $histories = $this->historyBudgetManager->getHistories($searchRequest);

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

    public function balancing(BudgetAccountBalanceRequest $budgetAccountBalance): void
    {
        $budget  = $budgetAccountBalance->getBudget();
        $account = $budgetAccountBalance->getAccount();

        if ($budget->hasPositiveCashFlow() || $budget->hasNegativeCashFlow()) {
            $entryBalanceSpent = new Entry()
                ->setAccount($account)
                ->setName($budget->getName())
                ->addFlag(EntryFlagEnum::BALANCE)
                ->setAmount($budget->getCashFlow());

            $entryBalanceForecast = new Entry()
                ->setAccount($account)
                ->setBudget($budget)
                ->setName($budget->getName())
                ->addFlag(EntryFlagEnum::BALANCE)
                ->setAmount(-$budget->getCashFlow());

            $budget->addEntry($entryBalanceForecast);

            $this->entryManager->create($entryBalanceSpent);
            $this->entityManager->flush();
        }
    }
}
