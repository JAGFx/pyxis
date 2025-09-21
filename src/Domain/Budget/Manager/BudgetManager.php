<?php

namespace App\Domain\Budget\Manager;

use App\Domain\Budget\Entity\Budget;
use App\Domain\Budget\Message\Command\BudgetCreateOrUpdateCommand;
use App\Domain\Budget\Message\Command\BudgetToggleEnableCommand;
use App\Domain\Budget\Message\Query\BudgetSearchQuery;
use App\Domain\Budget\Repository\BudgetRepository;
use App\Domain\Budget\ValueObject\BudgetValueObject;
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

    public function create(BudgetCreateOrUpdateCommand $command, bool $flush = true): void
    {
        /** @var Budget $budget */
        $budget = $this->objectMapper->map($command, Budget::class);

        $this->repository->create($budget);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function update(BudgetCreateOrUpdateCommand $command, bool $flush = true): void
    {
        $this->objectMapper->map($command, $command->getOrigin());

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function toggle(BudgetToggleEnableCommand $command, bool $flush = true): void
    {
        $budget = $command->getBudget();
        $budget->setEnabled(!$budget->isEnabled());

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    /**
     * @return Budget[]
     */
    public function getBudgets(?BudgetSearchQuery $searchQuery = null): array
    {
        $searchQuery ??= new BudgetSearchQuery();

        /** @var Budget[] $result */
        $result = $this->repository
            ->getBudgetsQueryBuilder($searchQuery)
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * @return BudgetValueObject[]
     */
    public function getBudgetValuesObject(?BudgetSearchQuery $searchQuery = null): array
    {
        $searchQuery ??= new BudgetSearchQuery();

        /** @var BudgetValueObject[] $result */
        $result = $this->repository
            ->getBudgetValueObjectsQueryBuilder($searchQuery)
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function find(int $id): ?Budget
    {
        /** @var ?Budget $budget */
        $budget = $this->repository->find($id);

        return $budget;
    }
}
