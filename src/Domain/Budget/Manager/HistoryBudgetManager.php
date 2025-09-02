<?php

namespace App\Domain\Budget\Manager;

use App\Domain\Budget\Entity\HistoryBudget;
use App\Domain\Budget\Model\BudgetSearchCommand;
use App\Domain\Budget\Model\HistoryBudgetSearchCommand;
use App\Domain\Budget\Repository\HistoryBudgetRepository;

class HistoryBudgetManager
{
    public function __construct(
        private readonly HistoryBudgetRepository $repository,
    ) {
    }

    public function create(HistoryBudget $historyBudget): self
    {
        $this->repository
            ->create($historyBudget)
            ->flush();

        return $this;
    }

    /**
     * @return string[]
     */
    public function getAvailableYears(): array
    {
        /** @var string[] $years */
        $years = $this->repository
            ->getAvailableYear()
            ->getQuery()
            ->getSingleColumnResult();

        return $years;
    }

    /**
     * @return HistoryBudget[]
     */
    public function getHistories(BudgetSearchCommand|HistoryBudgetSearchCommand $command): array
    {
        /** @var HistoryBudget[] $histories */
        $histories = $this->repository
            ->getHistoryBudgetsQueryBuilder($command)
            ->getQuery()
            ->getResult();

        return $histories;
    }
}
