<?php

namespace App\Domain\Budget\Manager;

use App\Domain\Budget\Entity\Budget;
use App\Domain\Budget\Message\Command\CreateOrUpdateBudgetCommand;
use App\Domain\Budget\Message\Command\ToggleEnableBudgetCommand;
use App\Domain\Budget\Repository\BudgetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

readonly class BudgetManager
{
    public function __construct(
        private BudgetRepository $repository,
        private EntityManagerInterface $entityManager,
        private ObjectMapperInterface $objectMapper,
    ) {
    }

    public function create(CreateOrUpdateBudgetCommand $command, bool $flush = true): void
    {
        /** @var Budget $budget */
        $budget = $this->objectMapper->map($command, Budget::class);

        $this->repository->create($budget);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function update(CreateOrUpdateBudgetCommand $command, bool $flush = true): void
    {
        $this->objectMapper->map($command, $command->getOrigin());

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function toggle(ToggleEnableBudgetCommand $command, bool $flush = true): void
    {
        $budget = $command->getBudget();
        $budget->setEnabled(!$budget->isEnabled());

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function find(int $id): ?Budget
    {
        /** @var ?Budget $budget */
        $budget = $this->repository->find($id);

        return $budget;
    }
}
