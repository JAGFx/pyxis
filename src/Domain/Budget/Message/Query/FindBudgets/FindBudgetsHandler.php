<?php

namespace App\Domain\Budget\Message\Query\FindBudgets;

use App\Domain\Budget\Entity\Budget;
use App\Domain\Budget\Repository\BudgetRepository;
use App\Shared\Cqs\Handler\QueryHandlerInterface;

/**
 * @see FindBudgetsQuery
 */
readonly class FindBudgetsHandler implements QueryHandlerInterface
{
    public function __construct(
        private BudgetRepository $repository,
    ) {
    }

    /**
     * @return Budget[]
     */
    public function __invoke(FindBudgetsQuery $query): array
    {
        /** @var Budget[] $result */
        $result = $this->repository
            ->getBudgetsQueryBuilder($query)
            ->getQuery()
            ->getResult();

        return $result;
    }
}
