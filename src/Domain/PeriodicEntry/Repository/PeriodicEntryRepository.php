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

    public function create(PeriodicEntry $entry, bool $flush = true): self
    {
        $this->_em->persist($entry);

        if ($flush) {
            $this->_em->flush();
        }

        return $this;
    }

    public function update(PeriodicEntry $entry, bool $flush = true): void
    {
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function remove(PeriodicEntry $entry, bool $flush = true): self
    {
        $this->_em->remove($entry);

        if ($flush) {
            $this->_em->flush();
        }

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

        // TODO: Add test for this
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
