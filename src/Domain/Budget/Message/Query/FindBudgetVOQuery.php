<?php

namespace App\Domain\Budget\Message\Query;

use App\Shared\Cqs\Message\Query\QueryInterface;

class FindBudgetVOQuery implements QueryInterface
{
    public function __construct(
        private ?int $year = null,
        private ?bool $showCredits = null,
        private ?string $name = null,
        private ?int $budgetId = null,
    ) {
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(?int $year): FindBudgetVOQuery
    {
        $this->year = $year;

        return $this;
    }

    public function getShowCredits(): ?bool
    {
        return $this->showCredits;
    }

    public function setShowCredits(?bool $showCredits): FindBudgetVOQuery
    {
        $this->showCredits = $showCredits;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): FindBudgetVOQuery
    {
        $this->name = $name;

        return $this;
    }

    public function getBudgetId(): ?int
    {
        return $this->budgetId;
    }

    public function setBudgetId(?int $budgetId): FindBudgetVOQuery
    {
        $this->budgetId = $budgetId;

        return $this;
    }
}
