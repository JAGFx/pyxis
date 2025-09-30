<?php

namespace App\Shared\Message\Query\GetBudgetBalanceProgresses;

use App\Shared\Cqs\Message\Query\QueryInterface;

/**
 * @see GetBudgetBalanceProgressesHandler
 */
class GetBudgetBalanceProgressesQuery implements QueryInterface
{
    public function __construct(
        private int $year,
        private ?bool $showCredits = null,
    ) {
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function setYear(int $year): GetBudgetBalanceProgressesQuery
    {
        $this->year = $year;

        return $this;
    }

    public function isShowCredits(): ?bool
    {
        return $this->showCredits;
    }

    public function setShowCredits(?bool $showCredits): GetBudgetBalanceProgressesQuery
    {
        $this->showCredits = $showCredits;

        return $this;
    }
}
