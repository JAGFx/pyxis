<?php

namespace App\Domain\Budget\Message\Command;

use App\Domain\Account\Entity\Account;
use App\Domain\Budget\Entity\Budget;
use App\Shared\Cqs\Message\Command\CommandInterface;
use Symfony\Component\Validator\Constraints as Assert;

class BudgetAccountBalanceCommand implements CommandInterface
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

    public function setBudget(Budget $budget): BudgetAccountBalanceCommand
    {
        $this->budget = $budget;

        return $this;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): BudgetAccountBalanceCommand
    {
        $this->account = $account;

        return $this;
    }
}
