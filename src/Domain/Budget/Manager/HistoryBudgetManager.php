<?php

namespace App\Domain\Budget\Manager;

use App\Domain\Budget\Entity\HistoryBudget;
use App\Domain\Budget\Message\Command\CreateHistoryBudgetCommand;
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
}
