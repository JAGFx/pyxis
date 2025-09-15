<?php

namespace App\Domain\Budget\ValueObject;

use App\Domain\Account\Entity\Account;
use App\Domain\Budget\Entity\Budget;

readonly class BudgetCashFlowByAccountValueObject
{
    public function __construct(
        private Budget $budget,
        private Account $account,
        private float $cashFlow,
    ) {
    }

    public function getBudget(): Budget
    {
        return $this->budget;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function getCashFlow(): float
    {
        return $this->cashFlow;
    }
}
