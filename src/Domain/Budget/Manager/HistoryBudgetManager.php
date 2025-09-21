<?php

namespace App\Domain\Budget\Manager;

use App\Domain\Budget\Entity\HistoryBudget;
use App\Domain\Budget\Message\Command\HistoryCreateCommand;
use App\Domain\Budget\Message\Query\BudgetSearchQuery;
use App\Domain\Budget\Message\Query\HistoryBudgetSearchQuery;
use App\Domain\Budget\Repository\HistoryBudgetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

readonly class HistoryBudgetManager
{
    public function __construct(
        private HistoryBudgetRepository $repository,
        private EntityManagerInterface $entityManager,
        private ObjectMapperInterface $objectMapper,
    ) {
    }

    public function create(HistoryCreateCommand $command, bool $flush = true): void
    {
        /** @var HistoryBudget $historyBudget */
        $historyBudget = $this->objectMapper->map($command, HistoryBudget::class);

        $this->repository->create($historyBudget);

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
    public function getHistories(BudgetSearchQuery|HistoryBudgetSearchQuery $searchQuery): array
    {
        /** @var HistoryBudget[] $histories */
        $histories = $this->repository
            ->getHistoryBudgetsQueryBuilder($searchQuery)
            ->getQuery()
            ->getResult();

        return $histories;
    }
}
