<?php

namespace App\Domain\Budget\Manager;

use App\Domain\Budget\Entity\Budget;
use App\Domain\Budget\Repository\BudgetRepository;
use App\Domain\Budget\Request\BudgetSearchRequest;
use App\Domain\Budget\ValueObject\BudgetValueObject;
use Doctrine\ORM\EntityManagerInterface;

readonly class BudgetManager
{
    public function __construct(
        private BudgetRepository $repository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function create(Budget $entity, bool $flush = true): void
    {
        $this->repository->create($entity);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function update(bool $flush = true): void
    {
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function toggle(Budget $budget, bool $flush = true): void
    {
        $budget->setEnabled(!$budget->isEnabled());

        $this->update($flush);
    }

    /**
     * @return Budget[]
     */
    public function getBudgets(?BudgetSearchRequest $searchRequest = null): array
    {
        $searchRequest ??= new BudgetSearchRequest();

        /** @var Budget[] $result */
        $result = $this->repository
            ->getBudgetsQueryBuilder($searchRequest)
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * @return BudgetValueObject[]
     */
    public function getBudgetValuesObject(?BudgetSearchRequest $searchRequest = null): array
    {
        $searchRequest ??= new BudgetSearchRequest();

        /** @var BudgetValueObject[] $result */
        $result = $this->repository
            ->getBudgetValueObjectsQueryBuilder($searchRequest)
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
