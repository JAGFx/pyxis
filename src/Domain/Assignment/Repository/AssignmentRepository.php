<?php

namespace App\Domain\Assignment\Repository;

use App\Domain\Assignment\Entity\Assignment;
use App\Domain\Assignment\Message\Query\FindAssignmentsQuery;
use App\Domain\Assignment\Message\Query\GetAssignmentBalanceQuery;
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

        if (!is_null($searchQuery->getAccount())) {
            $queryBuilder
                ->andWhere('a.account = :account')
                ->setParameter('account', $searchQuery->getAccount());
        }

        if (!is_null($searchQuery->getName())) {
            $queryBuilder
                ->andWhere('a.name LIKE :name')
                ->setParameter('name', '%' . $searchQuery->getName() . '%');
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

        if (!is_null($query->getAccount())) {
            $queryBuilder
                ->andWhere('a.account = :account')
                ->setParameter('account', $query->getAccount());
        }

        return $queryBuilder;
    }

    public function create(Assignment $entity): self
    {
        $this->getEntityManager()->persist($entity);

        return $this;
    }

    public function remove(Assignment $entry): self
    {
        $this->getEntityManager()->remove($entry);

        return $this;
    }
}
