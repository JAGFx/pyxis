<?php

namespace App\Domain\Budget\Repository;

use App\Domain\Budget\Entity\Budget;
use App\Domain\Budget\Message\Query\FindBudgets\FindBudgetsQuery;
use App\Domain\Budget\Message\Query\FindBudgetVO\FindBudgetVOQuery;
use App\Shared\Utils\YearRange;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Budget>
 */
class BudgetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Budget::class);
    }

    public function getBudgetsQueryBuilder(FindBudgetsQuery $searchQuery): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('b')
            ->orderBy('b.name');

        if (null !== $searchQuery->getName()) {
            $queryBuilder
                ->andWhere('b.name LIKE :name')
                ->setParameter('name', '%' . $searchQuery->getName() . '%');
        }

        if (!is_null($searchQuery->isEnabled())) {
            $queryBuilder
                ->andWhere('b.enabled = :enable')
                ->setParameter('enable', $searchQuery->isEnabled())
            ;
        }

        switch ($searchQuery->getOrderBy()) {
            case 'name':
                $queryBuilder->orderBy('b.name', $searchQuery->getOrderDirection()->value);
                break;
            default:
                $queryBuilder->orderBy('b.id', $searchQuery->getOrderDirection()->value);
                break;
        }

        return $queryBuilder;
    }

    public function getBudgetValueObjectsQueryBuilder(FindBudgetVOQuery $searchQuery): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('b')
            ->select(
                'NEW App\Domain\Budget\ValueObject\BudgetValueObject(b.id, b.name, b.amount, b.enabled, SUM(e.amount))'
            )
            ->join('b.entries', 'e')
            ->groupBy('b.id');

        if (null !== $searchQuery->getYear()) {
            $queryBuilder->andWhere('e.createdAt BETWEEN :from AND :to')
                ->setParameter('from', YearRange::firstDayOf($searchQuery->getYear())->format('Y-m-d H:i:s'))
                ->setParameter('to', YearRange::lastDayOf($searchQuery->getYear())->format('Y-m-d H:i:s'));
        }

        if (true === $searchQuery->getShowCredits()) {
            $queryBuilder
                ->andWhere('e.amount > 0')
                ->andWhere('JSON_LENGTH(e.flags) = 0')
            ;
        }

        if (false === $searchQuery->getShowCredits()) {
            $queryBuilder
                ->andWhere('e.amount < 0')
                ->andWhere('JSON_LENGTH(e.flags) = 0')
            ;
        }

        if (null !== $searchQuery->getName()) {
            $queryBuilder->andWhere('b.name = :name')
                ->setParameter('name', $searchQuery->getName());
        }

        if (null !== $searchQuery->getBudgetId()) {
            $queryBuilder->andWhere('b.id = :budget')
                ->setParameter('budget', $searchQuery->getBudgetId());
        }

        return $queryBuilder;
    }
}
