<?php

namespace App\Domain\Budget\Message\Query\FindHistoryBudgets;

use App\Domain\Budget\Entity\Budget;
use App\Shared\Cqs\Message\Query\QueryInterface;

// TODO: Add a way to fail linter if implement right interface

/**
 * @see FindHistoryBudgetsHandler
 */
class FindHistoryBudgetsQuery implements QueryInterface
{
    public function __construct(
        private ?Budget $budget = null,
        private ?int $year = null,
    ) {
    }

    public function getBudget(): ?Budget
    {
        return $this->budget;
    }

    public function setBudget(?Budget $budget): FindHistoryBudgetsQuery
    {
        $this->budget = $budget;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(?int $year): FindHistoryBudgetsQuery
    {
        $this->year = $year;

        return $this;
    }
}
