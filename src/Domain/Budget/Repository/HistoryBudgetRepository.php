<?php

namespace App\Domain\Budget\Repository;

use App\Domain\Budget\Entity\HistoryBudget;
use App\Domain\Budget\Model\BudgetSearchCommand;
use App\Domain\Budget\Model\HistoryBudgetSearchCommand;
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

    public function getHistoryBudgetsQueryBuilder(BudgetSearchCommand|HistoryBudgetSearchCommand $command): QueryBuilder
    {
        $qb = $this->createQueryBuilder('hb');

        if (!is_null($command->getYear())) {
            $qb
                ->andWhere('hb.date BETWEEN :from AND :to')
                ->setParameter('from', YearRange::firstDayOf($command->getYear())->format('Y-m-d H:i:s'))
                ->setParameter('to', YearRange::lastDayOf($command->getYear())->format('Y-m-d H:i:s'));
        }

        if ($command instanceof HistoryBudgetSearchCommand && !is_null($command->getBudget())) {
            $qb
                ->andWhere('hb.budget = :budget')
                ->setParameter('budget', $command->getBudget());
        }

        return $qb;
    }

    public function create(HistoryBudget $historyBudget): self
    {
        $this->_em->persist($historyBudget);

        return $this;
    }

    public function flush(): void
    {
        $this->_em->flush();
    }
}
