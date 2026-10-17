<?php

namespace App\Domain\Assignment\Repository;

use App\Domain\Assignment\Entity\Assignment;
use App\Domain\Assignment\Message\Query\FindAssignments\FindAssignmentsQuery;
use App\Domain\Assignment\Message\Query\GetAssignmentBalance\GetAssignmentBalanceQuery;
use App\Domain\Entry\Entity\Entry;
use App\Infrastructure\Doctrine\Query\LikeExpression;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Assignment>
 */
class AssignmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Assignment::class);
    }

    public function getAssignmentsQueryBuilder(FindAssignmentsQuery $searchQuery): QueryBuilder
    {
        $queryBuilder = $this
            ->createQueryBuilder('a')
        ;

        if (!is_null($searchQuery->getAccountId())) {
            $queryBuilder
                ->andWhere('a.account = :account')
                ->setParameter('account', $searchQuery->getAccountId());
        }

        if (!is_null($searchQuery->getName())) {
            $queryBuilder
                ->andWhere('a.name LIKE :name')
                ->setParameter('name', LikeExpression::contains($searchQuery->getName()));
        }

        switch ($searchQuery->getOrderBy()) {
            case 'name':
                $queryBuilder->orderBy('a.name', $searchQuery->getOrderDirection()->value);
                break;
            default:
                $queryBuilder->orderBy('a.id', $searchQuery->getOrderDirection()->value);
                break;
        }

        return $queryBuilder;
    }

    public function balanceQueryBuilder(GetAssignmentBalanceQuery $query): QueryBuilder
    {
        $queryBuilder = $this
            ->createQueryBuilder('a')
            ->select('SUM(a.amount) as sum');

        if (!is_null($query->getAccountId())) {
            $queryBuilder
                ->andWhere('a.account = :account')
                ->setParameter('account', $query->getAccountId());
        }

        return $queryBuilder;
    }

    /**
     * Sum of the entries linked to the assignments: added to the starting amounts to get the balances.
     */
    public function linkedEntriesQueryBuilder(GetAssignmentBalanceQuery $query): QueryBuilder
    {
        $queryBuilder = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('SUM(e.amount) as sum')
            ->from(Entry::class, 'e')
            ->join('e.assignment', 'a');

        if (!is_null($query->getAccountId())) {
            $queryBuilder
                ->andWhere('a.account = :account')
                ->setParameter('account', $query->getAccountId());
        }

        return $queryBuilder;
    }
}
