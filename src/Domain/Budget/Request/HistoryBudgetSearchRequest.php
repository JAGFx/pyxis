<?php

namespace App\Domain\Budget\Request;

use App\Domain\Budget\Entity\Budget;

class HistoryBudgetSearchRequest
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

    public function setBudget(?Budget $budget): HistoryBudgetSearchRequest
    {
        $this->budget = $budget;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(?int $year): HistoryBudgetSearchRequest
    {
        $this->year = $year;

        return $this;
    }
}
