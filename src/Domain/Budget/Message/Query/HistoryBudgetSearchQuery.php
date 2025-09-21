<?php

namespace App\Domain\Budget\Message\Query;

use App\Domain\Budget\Entity\Budget;

class HistoryBudgetSearchQuery
{
    public function __construct(
        private ?Budget $budget,
        private ?int $year,
    ) {
    }

    public function getBudget(): ?Budget
    {
        return $this->budget;
    }

    public function setBudget(?Budget $budget): HistoryBudgetSearchQuery
    {
        $this->budget = $budget;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(?int $year): HistoryBudgetSearchQuery
    {
        $this->year = $year;

        return $this;
    }
}
