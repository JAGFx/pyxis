<?php

namespace App\Domain\Budget\Manager;

use App\Domain\Budget\Entity\Budget;
use App\Domain\Budget\Repository\BudgetRepository;

readonly class BudgetManager
{
    public function __construct(
        private BudgetRepository $repository,
    ) {
    }

    public function find(int $id): ?Budget
    {
        /** @var ?Budget $budget */
        $budget = $this->repository->find($id);

        return $budget;
    }
}
