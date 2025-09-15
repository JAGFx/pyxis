<?php

namespace App\Domain\Budget\Request;

use App\Domain\Account\Entity\Account;
use App\Domain\Budget\Entity\Budget;
use Symfony\Component\Validator\Constraints as Assert;

class BudgetAccountBalanceRequest
{
    public function __construct(
        private Budget $budget,

        #[Assert\NotNull]
        private ?Account $account = null,
    ) {
    }

    public function getBudget(): Budget
    {
        return $this->budget;
    }

    public function setBudget(Budget $budget): BudgetAccountBalanceRequest
    {
        $this->budget = $budget;

        return $this;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): BudgetAccountBalanceRequest
    {
        $this->account = $account;

        return $this;
    }
}
