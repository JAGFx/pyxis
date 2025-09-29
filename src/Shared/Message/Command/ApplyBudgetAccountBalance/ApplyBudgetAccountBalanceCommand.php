<?php

namespace App\Shared\Message\Command\ApplyBudgetAccountBalance;

use App\Domain\Account\Entity\Account;
use App\Domain\Budget\Entity\Budget;
use App\Shared\Cqs\Message\Command\CommandInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @see ApplyBudgetAccountBalanceHandler
 */
class ApplyBudgetAccountBalanceCommand implements CommandInterface
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

    public function setBudget(Budget $budget): ApplyBudgetAccountBalanceCommand
    {
        $this->budget = $budget;

        return $this;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): ApplyBudgetAccountBalanceCommand
    {
        $this->account = $account;

        return $this;
    }
}
