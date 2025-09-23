<?php

namespace App\Domain\Budget\Message\Query\FindBudgetVO;

use App\Domain\Budget\Repository\BudgetRepository;
use App\Domain\Budget\ValueObject\BudgetValueObject;
use App\Shared\Cqs\Handler\QueryHandlerInterface;

/**
 * @see FindBudgetVOQuery
 */
readonly class FindBudgetVOHandler implements QueryHandlerInterface
{
    public function __construct(
        private BudgetRepository $repository,
    ) {
    }

    /**
     * @return BudgetValueObject[]
     */
    public function __invoke(FindBudgetVOQuery $query): array
    {
        /** @var BudgetValueObject[] $result */
        $result = $this->repository
            ->getBudgetValueObjectsQueryBuilder($query)
            ->getQuery()
            ->getResult();

        return $result;
    }
}
