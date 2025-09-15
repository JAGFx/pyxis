<?php

namespace App\Domain\PeriodicEntry\Repository;

use App\Domain\Entry\Entity\EntryTypeEnum;
use App\Domain\PeriodicEntry\DTO\PeriodicEntrySearchCommand;
use App\Domain\PeriodicEntry\Entity\PeriodicEntry;
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

    public function getPeriodicEntriesQueryBuilder(?PeriodicEntrySearchCommand $command = null): QueryBuilder
    {
        $command ??= new PeriodicEntrySearchCommand();

        $queryBuilder = $this
            ->createQueryBuilder('p');

        if (null !== $command->getName()) {
            $queryBuilder
                ->andWhere('p.name LIKE :name')
                ->setParameter('name', '%' . $command->getName() . '%')
            ;
        }

        if (EntryTypeEnum::TYPE_SPENT === $command->getEntryTypeEnum()) {
            $queryBuilder
                ->andWhere('p.budgets IS EMPTY')
                ->andWhere('p.amount IS NOT NULL')
            ;
        }

        if (EntryTypeEnum::TYPE_FORECAST === $command->getEntryTypeEnum()) {
            $queryBuilder
                ->andWhere('p.budgets IS NOT EMPTY')
                ->andWhere('p.amount IS NULL')
            ;
        }

        switch ($command->getOrderBy()) {
            case 'name':
                $queryBuilder->orderBy('p.name', $command->getOrderDirection()->value);
                break;
            default:
                $queryBuilder->orderBy('p.id', $command->getOrderDirection()->value);
                break;
        }

        return $queryBuilder;
    }
}
