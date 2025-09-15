<?php

namespace App\Domain\Budget\Manager;

use App\Domain\Budget\Entity\HistoryBudget;
use App\Domain\Budget\Repository\HistoryBudgetRepository;
use App\Domain\Budget\Request\BudgetSearchRequest;
use App\Domain\Budget\Request\HistoryBudgetSearchRequest;
use Doctrine\ORM\EntityManagerInterface;

readonly class HistoryBudgetManager
{
    public function __construct(
        private HistoryBudgetRepository $repository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function create(HistoryBudget $entity, bool $flush = true): void
    {
        $this->repository->create($entity);

        if ($flush) {
            $this->entityManager->flush();
        }
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
    public function getHistories(BudgetSearchRequest|HistoryBudgetSearchRequest $searchRequest): array
    {
        /** @var HistoryBudget[] $histories */
        $histories = $this->repository
            ->getHistoryBudgetsQueryBuilder($searchRequest)
            ->getQuery()
            ->getResult();

        return $histories;
    }
}
