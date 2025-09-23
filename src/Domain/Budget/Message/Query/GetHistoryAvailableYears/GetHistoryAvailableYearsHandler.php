<?php

namespace App\Domain\Budget\Message\Query\GetHistoryAvailableYears;

use App\Domain\Budget\Repository\HistoryBudgetRepository;
use App\Shared\Cqs\Handler\QueryHandlerInterface;

/**
 * @see GetHistoryAvailableYearsQuery
 */
readonly class GetHistoryAvailableYearsHandler implements QueryHandlerInterface
{
    public function __construct(
        private HistoryBudgetRepository $repository,
    ) {
    }

    /**
     * @return string[]
     */
    public function __invoke(GetHistoryAvailableYearsQuery $query): array
    {
        /** @var string[] $years */
        $years = $this->repository
            ->getAvailableYear()
            ->getQuery()
            ->getSingleColumnResult();

        return $years;
    }
}
