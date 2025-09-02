<?php

namespace App\Domain\Assignment\Repository;

use App\Domain\Assignment\Entity\Assignment;
use App\Domain\Assignment\Model\AssignmentSearchCommand;
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
