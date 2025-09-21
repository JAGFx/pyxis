<?php

namespace App\Domain\Budget\Message\Command;

use App\Domain\Budget\Entity\Budget;
use App\Shared\Cqs\Message\Command\CommandInterface;

class BudgetToggleEnableCommand implements CommandInterface
{
    public function __construct(
        private Budget $budget,
    ) {
    }

    public function getBudget(): Budget
    {
        return $this->budget;
    }

    public function setBudget(Budget $budget): BudgetToggleEnableCommand
    {
        $this->budget = $budget;

        return $this;
    }
}
