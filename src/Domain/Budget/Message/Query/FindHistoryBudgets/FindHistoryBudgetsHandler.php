<?php

namespace App\Domain\Budget\Message\Query\FindHistoryBudgets;

use App\Domain\Budget\Entity\HistoryBudget;
use App\Domain\Budget\Repository\HistoryBudgetRepository;
use App\Shared\Cqs\Handler\QueryHandlerInterface;

/**
 * @see FindHistoryBudgetsQuery
 */
readonly class FindHistoryBudgetsHandler implements QueryHandlerInterface
{
    public function __construct(
        private HistoryBudgetRepository $repository,
    ) {
    }

    /**
     * @return HistoryBudget[]
     */
    public function __invoke(FindHistoryBudgetsQuery $query): array
    {
        /** @var HistoryBudget[] $histories */
        $histories = $this->repository
            ->getHistoryBudgetsQueryBuilder($query)
            ->getQuery()
            ->getResult();

        return $histories;
    }
}
