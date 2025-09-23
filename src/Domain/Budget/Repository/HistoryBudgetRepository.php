<?php

namespace App\Domain\Budget\Repository;

use App\Domain\Budget\Entity\HistoryBudget;
use App\Domain\Budget\Message\Query\FindHistoryBudgets\FindHistoryBudgetsQuery;
use App\Shared\Utils\YearRange;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HistoryBudget>
 */
class HistoryBudgetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HistoryBudget::class);
    }

    public function getAvailableYear(): QueryBuilder
    {
        return $this
            ->createQueryBuilder('hb')
            ->select('DISTINCT YEAR(hb.date) AS year')
            ->orderBy('year', 'DESC');
    }

    public function getHistoryBudgetsQueryBuilder(FindHistoryBudgetsQuery $searchQuery): QueryBuilder
    {
        $qb = $this->createQueryBuilder('hb');

        if (!is_null($searchQuery->getYear())) {
            $qb
                ->andWhere('hb.date BETWEEN :from AND :to')
                ->setParameter('from', YearRange::firstDayOf($searchQuery->getYear())->format('Y-m-d H:i:s'))
                ->setParameter('to', YearRange::lastDayOf($searchQuery->getYear())->format('Y-m-d H:i:s'));
        }

        if (!is_null($searchQuery->getBudget())) {
            $qb
                ->andWhere('hb.budget = :budget')
                ->setParameter('budget', $searchQuery->getBudget());
        }

        return $qb;
    }

    public function create(HistoryBudget $historyBudget): self
    {
        $this->getEntityManager()->persist($historyBudget);

        return $this;
    }
}
