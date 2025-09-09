<?php

namespace App\Domain\Assignment\Repository;

use App\Domain\Assignment\DTO\AssignmentSearchCommand;
use App\Domain\Assignment\Entity\Assignment;
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

    public function getAssignmentsQueryBuilder(AssignmentSearchCommand $command): QueryBuilder
    {
        $queryBuilder = $this
            ->createQueryBuilder('a')
        ;

        if (!is_null($command->getAccount())) {
            $queryBuilder
                ->andWhere('a.account = :account')
                ->setParameter('account', $command->getAccount());
        }

        if (!is_null($command->getName())) {
            $queryBuilder
                ->andWhere('a.name LIKE :name')
                ->setParameter('name', '%' . $command->getName() . '%');
        }

        switch ($command->getOrderBy()) {
            case 'name':
                $queryBuilder->orderBy('a.name', $command->getOrderDirection()->value);
                break;
            default:
                $queryBuilder->orderBy('a.id', $command->getOrderDirection()->value);
                break;
        }

        return $queryBuilder;
    }

    public function balanceQueryBuilder(AssignmentSearchCommand $command): QueryBuilder
    {
        return $this
            ->getAssignmentsQueryBuilder($command)
            ->select('SUM(a.amount) as sum');
    }

    public function create(Assignment $entity): self
    {
        $this->_em->persist($entity);

        return $this;
    }

    public function flush(): void
    {
        $this->_em->flush();
    }

    public function remove(Assignment $entry): self
    {
        $this->_em->remove($entry);

        return $this;
    }
}
