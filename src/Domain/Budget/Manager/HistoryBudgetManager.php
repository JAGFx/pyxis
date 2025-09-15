<?php

namespace App\Domain\Budget\Manager;

use App\Domain\Budget\DTO\BudgetSearchCommand;
use App\Domain\Budget\DTO\HistoryBudgetSearchCommand;
use App\Domain\Budget\Entity\HistoryBudget;
use App\Domain\Budget\Repository\HistoryBudgetRepository;
use Doctrine\ORM\EntityManagerInterface;

class HistoryBudgetManager
{
    public function __construct(
        private readonly HistoryBudgetRepository $repository,
        private readonly EntityManagerInterface $entityManager,
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
