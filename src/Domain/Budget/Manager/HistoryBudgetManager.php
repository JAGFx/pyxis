<?php

namespace App\Domain\Budget\Manager;

use App\Domain\Budget\Entity\HistoryBudget;
use App\Domain\Budget\Message\Command\CreateHistoryBudgetCommand;
use App\Domain\Budget\Message\Query\FindHistoryBudgetsQuery;
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

    public function create(CreateHistoryBudgetCommand $command, bool $flush = true): void
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
    public function getHistories(FindHistoryBudgetsQuery $searchQuery): array
    {
        /** @var HistoryBudget[] $histories */
        $histories = $this->repository
            ->getHistoryBudgetsQueryBuilder($searchQuery)
            ->getQuery()
            ->getResult();

        return $histories;
    }
}
