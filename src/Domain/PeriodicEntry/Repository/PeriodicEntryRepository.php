<?php

namespace App\Domain\PeriodicEntry\Repository;

use App\Domain\Entry\Entity\EntryTypeEnum;
use App\Domain\PeriodicEntry\Entity\PeriodicEntry;
use App\Domain\PeriodicEntry\Request\PeriodicEntrySearchRequest;
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

    public function getPeriodicEntriesQueryBuilder(?PeriodicEntrySearchRequest $searchRequest = null): QueryBuilder
    {
        $searchRequest ??= new PeriodicEntrySearchRequest();

        $queryBuilder = $this
            ->createQueryBuilder('p');

        if (null !== $searchRequest->getName()) {
            $queryBuilder
                ->andWhere('p.name LIKE :name')
                ->setParameter('name', '%' . $searchRequest->getName() . '%')
            ;
        }

        if (EntryTypeEnum::TYPE_SPENT === $searchRequest->getEntryTypeEnum()) {
            $queryBuilder
                ->andWhere('p.budgets IS EMPTY')
                ->andWhere('p.amount IS NOT NULL')
            ;
        }

        if (EntryTypeEnum::TYPE_FORECAST === $searchRequest->getEntryTypeEnum()) {
            $queryBuilder
                ->andWhere('p.budgets IS NOT EMPTY')
                ->andWhere('p.amount IS NULL')
            ;
        }

        switch ($searchRequest->getOrderBy()) {
            case 'name':
                $queryBuilder->orderBy('p.name', $searchRequest->getOrderDirection()->value);
                break;
            default:
                $queryBuilder->orderBy('p.id', $searchRequest->getOrderDirection()->value);
                break;
        }

        return $queryBuilder;
    }
}
