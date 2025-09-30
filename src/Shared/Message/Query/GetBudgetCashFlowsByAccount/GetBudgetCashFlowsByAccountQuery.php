<?php

namespace App\Shared\Message\Query\GetBudgetCashFlowsByAccount;

use App\Shared\Cqs\Message\Query\QueryInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @see GetBudgetCashFlowsByAccountHandler
 */
class GetBudgetCashFlowsByAccountQuery implements QueryInterface
{
    public function __construct(
        #[Assert\NotNull]
        private ?int $budgetId = null,
    ) {
    }

    public function getBudgetId(): ?int
    {
        return $this->budgetId;
    }

    public function setBudgetId(?int $budgetId): GetBudgetCashFlowsByAccountQuery
    {
        $this->budgetId = $budgetId;

        return $this;
    }
}
