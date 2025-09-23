<?php

namespace App\Domain\PeriodicEntry\Repository;

use App\Domain\Entry\Entity\EntryTypeEnum;
use App\Domain\PeriodicEntry\Entity\PeriodicEntry;
use App\Domain\PeriodicEntry\Message\Query\FindPeriodicEntries\FindPeriodicEntriesQuery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PeriodicEntry>
 */
class PeriodicEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PeriodicEntry::class);
    }

    public function create(PeriodicEntry $entity): self
    {
        $this->getEntityManager()->persist($entity);

        return $this;
    }

    public function remove(PeriodicEntry $entry): self
    {
        $this->getEntityManager()->remove($entry);

        return $this;
    }

    public function getPeriodicEntriesQueryBuilder(?FindPeriodicEntriesQuery $searchQuery = null): QueryBuilder
    {
        $searchQuery ??= new FindPeriodicEntriesQuery();

        $queryBuilder = $this
            ->createQueryBuilder('p');

        if (null !== $searchQuery->getName()) {
            $queryBuilder
                ->andWhere('p.name LIKE :name')
                ->setParameter('name', '%' . $searchQuery->getName() . '%')
            ;
        }

        if (EntryTypeEnum::TYPE_SPENT === $searchQuery->getEntryTypeEnum()) {
            $queryBuilder
                ->andWhere('p.budgets IS EMPTY')
                ->andWhere('p.amount IS NOT NULL')
            ;
        }

        if (EntryTypeEnum::TYPE_FORECAST === $searchQuery->getEntryTypeEnum()) {
            $queryBuilder
                ->andWhere('p.budgets IS NOT EMPTY')
                ->andWhere('p.amount IS NULL')
            ;
        }

        switch ($searchQuery->getOrderBy()) {
            case 'name':
                $queryBuilder->orderBy('p.name', $searchQuery->getOrderDirection()->value);
                break;
            default:
                $queryBuilder->orderBy('p.id', $searchQuery->getOrderDirection()->value);
                break;
        }

        return $queryBuilder;
    }
}
