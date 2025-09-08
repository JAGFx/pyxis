<?php

namespace App\Domain\Budget\DTO;

use App\Domain\Budget\Entity\Budget;

class HistoryBudgetSearchCommand
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

    public function setBudget(?Budget $budget): HistoryBudgetSearchCommand
    {
        $this->budget = $budget;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(?int $year): HistoryBudgetSearchCommand
    {
        $this->year = $year;

        return $this;
    }
}
