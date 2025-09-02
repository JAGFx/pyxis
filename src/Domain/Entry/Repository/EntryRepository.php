<?php

namespace App\Domain\Entry\Repository;

use App\Domain\Entry\Entity\Entry;
use App\Domain\Entry\Model\EntrySearchCommand;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Entry>
 */
class EntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Entry::class);
    }

    public function create(Entry $entry): self
    {
        $this->_em->persist($entry);

        return $this;
    }

    public function remove(Entry $entry): self
    {
        $this->_em->remove($entry);

        return $this;
    }

    public function flush(): void
    {
        $this->_em->flush();
    }

    public function balance(EntrySearchCommand $command): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('e')
            ->select('SUM(e.amount) as sum, b.id')
            ->leftJoin('e.budget', 'b')
            ->groupBy('b.id');

        if (!is_null($command->getAccount())) {
            $queryBuilder
                ->andWhere('e.account = :account')
                ->setParameter('account', $command->getAccount());
        }

        return $queryBuilder;
    }

    public function getEntriesQueryBuilder(EntrySearchCommand $command): QueryBuilder
    {
        $queryBuilder = $this
            ->createQueryBuilder('e')
        ;

        if (!is_null($command->getStartDate()) && !is_null($command->getEndDate())) {
            $queryBuilder
                ->andWhere('e.createdAt BETWEEN :startDate AND :endDate')
                ->setParameter('startDate', $command->getStartDate()->format('Y-m-d'))
                ->setParameter('endDate', $command->getEndDate()->format('Y-m-d'));
        }

        switch ($command->getOrderBy()) {
            case 'createdAt':
                $queryBuilder->orderBy('e.createdAt', $command->getOrderDirection()->value);
                break;
            default:
                $queryBuilder->orderBy('a.id', $command->getOrderDirection()->value);
                break;
        }

        return $queryBuilder;
    }
}
