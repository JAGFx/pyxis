<?php

namespace App\Domain\Budget\Message\Query\FindHistoryBudgets;

use App\Shared\Cqs\Message\Query\QueryInterface;

// TODO: Add a way to fail linter if implement right interface

/**
 * @see FindHistoryBudgetsHandler
 */
class FindHistoryBudgetsQuery implements QueryInterface
{
    public function __construct(
        private ?int $budgetId = null,
        private ?int $year = null,
    ) {
    }

    public function getBudgetId(): ?int
    {
        return $this->budgetId;
    }

    public function setBudgetId(?int $budgetId): FindHistoryBudgetsQuery
    {
        $this->budgetId = $budgetId;

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
