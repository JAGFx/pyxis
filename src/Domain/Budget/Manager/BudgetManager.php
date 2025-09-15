<?php

namespace App\Domain\Budget\Manager;

use App\Domain\Budget\DTO\BudgetAccountBalance;
use App\Domain\Budget\DTO\BudgetSearchCommand;
use App\Domain\Budget\Entity\Budget;
use App\Domain\Budget\Repository\BudgetRepository;
use App\Domain\Budget\ValueObject\BudgetValueObject;
use App\Domain\Entry\Entity\Entry;
use App\Domain\Entry\Entity\EntryKindEnum;
use App\Domain\Entry\Manager\EntryManager;
use Doctrine\ORM\EntityManagerInterface;

readonly class BudgetManager
{
    public function __construct(
        private BudgetRepository $repository,
        private EntryManager $entryManager,
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
        $budget->setEnable(!$budget->getEnable());

        $this->update($flush);
    }

    /**
     * @return Budget[]
     */
    public function getBudgets(?BudgetSearchCommand $command = null): array
    {
        $command ??= new BudgetSearchCommand();

        /** @var Budget[] $result */
        $result = $this->repository
            ->getBudgetsQueryBuilder($command)
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * @return BudgetValueObject[]
     */
    public function getBudgetValuesObject(?BudgetSearchCommand $command = null): array
    {
        $command ??= new BudgetSearchCommand();

        /** @var BudgetValueObject[] $result */
        $result = $this->repository
            ->getBudgetValueObjectsQueryBuilder($command)
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

    public function balancing(BudgetAccountBalance $budgetAccountBalance): void
    {
        $budget  = $budgetAccountBalance->getBudget();
        $account = $budgetAccountBalance->getAccount();

        if ($budget->hasPositiveCashFlow() || $budget->hasNegativeCashFlow()) {
            $entryBalanceSpent = new Entry()
                ->setAccount($account)
                ->setName(sprintf('Équilibrage de %s', $budget->getName()))
                ->setKind(EntryKindEnum::BALANCING)
                ->setAmount($budget->getCashFlow());

            $entryBalanceForecast = new Entry()
                ->setAccount($account)
                ->setBudget($budget)
                ->setName(sprintf('Équilibrage de %s', $budget->getName()))
                ->setKind(EntryKindEnum::BALANCING)
                ->setAmount(-$budget->getCashFlow());

            $budget->addEntry($entryBalanceForecast);

            $this->entryManager->create($entryBalanceSpent);
            $this->update();
        }
    }
}
