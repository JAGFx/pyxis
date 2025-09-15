<?php

namespace App\Domain\Assignment\Repository;

use App\Domain\Assignment\Entity\Assignment;
use App\Domain\Assignment\Request\AssignmentSearchRequest;
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

    public function getAssignmentsQueryBuilder(AssignmentSearchRequest $searchRequest): QueryBuilder
    {
        $queryBuilder = $this
            ->createQueryBuilder('a')
        ;

        if (!is_null($searchRequest->getAccount())) {
            $queryBuilder
                ->andWhere('a.account = :account')
                ->setParameter('account', $searchRequest->getAccount());
        }

        if (!is_null($searchRequest->getName())) {
            $queryBuilder
                ->andWhere('a.name LIKE :name')
                ->setParameter('name', '%' . $searchRequest->getName() . '%');
        }

        switch ($searchRequest->getOrderBy()) {
            case 'name':
                $queryBuilder->orderBy('a.name', $searchRequest->getOrderDirection()->value);
                break;
            default:
                $queryBuilder->orderBy('a.id', $searchRequest->getOrderDirection()->value);
                break;
        }

        return $queryBuilder;
    }

    public function balanceQueryBuilder(AssignmentSearchRequest $searchRequest): QueryBuilder
    {
        return $this
            ->getAssignmentsQueryBuilder($searchRequest)
            ->select('SUM(a.amount) as sum');
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
