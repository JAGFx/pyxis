<?php

namespace App\Domain\Budget\Repository;

use App\Domain\Budget\Entity\Budget;
use App\Domain\Budget\Request\BudgetSearchRequest;
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

    public function create(Budget $budget): self
    {
        $this->getEntityManager()->persist($budget);

        return $this;
    }

    public function getBudgetsQueryBuilder(BudgetSearchRequest $searchRequest): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('b')
            ->orderBy('b.name');

        if (null !== $searchRequest->getName()) {
            $queryBuilder
                ->andWhere('b.name LIKE :name')
                ->setParameter('name', '%' . $searchRequest->getName() . '%');
        }

        if (!is_null($searchRequest->isEnabled())) {
            $queryBuilder
                ->andWhere('b.enabled = :enable')
                ->setParameter('enable', $searchRequest->isEnabled())
            ;
        }

        switch ($searchRequest->getOrderBy()) {
            case 'name':
                $queryBuilder->orderBy('b.name', $searchRequest->getOrderDirection()->value);
                break;
            default:
                $queryBuilder->orderBy('b.id', $searchRequest->getOrderDirection()->value);
                break;
        }

        return $queryBuilder;
    }

    public function getBudgetValueObjectsQueryBuilder(BudgetSearchRequest $searchRequest): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('b')
            ->select(
                'NEW App\Domain\Budget\ValueObject\BudgetValueObject(b.id, b.name, b.amount, b.enabled, SUM(e.amount))'
            )
            ->join('b.entries', 'e')
            ->groupBy('b.id');

        if (null !== $searchRequest->getYear()) {
            $queryBuilder->andWhere('e.createdAt BETWEEN :from AND :to')
                ->setParameter('from', YearRange::firstDayOf($searchRequest->getYear())->format('Y-m-d H:i:s'))
                ->setParameter('to', YearRange::lastDayOf($searchRequest->getYear())->format('Y-m-d H:i:s'));
        }

        if (true === $searchRequest->getShowCredits()) {
            $queryBuilder
                ->andWhere('e.amount > 0')
                ->andWhere('JSON_LENGTH(e.flags) = 0')
            ;
        }

        if (false === $searchRequest->getShowCredits()) {
            $queryBuilder
                ->andWhere('e.amount < 0')
                ->andWhere('JSON_LENGTH(e.flags) = 0')
            ;
        }

        if (null !== $searchRequest->getName()) {
            $queryBuilder->andWhere('b.name = :name')
                ->setParameter('name', $searchRequest->getName());
        }

        if (null !== $searchRequest->getBudgetId()) {
            $queryBuilder->andWhere('b.id = :budget')
                ->setParameter('budget', $searchRequest->getBudgetId());
        }

        return $queryBuilder;
    }
}
