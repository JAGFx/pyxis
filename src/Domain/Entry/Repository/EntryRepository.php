<?php

namespace App\Domain\Entry\Repository;

use App\Domain\Entry\Entity\Entry;
use App\Domain\Entry\Entity\EntryFlagEnum;
use App\Domain\Entry\Entity\EntryTypeEnum;
use App\Domain\Entry\Message\Query\FindEntries\FindEntriesQuery;
use App\Domain\Entry\Message\Query\GetEntryBalance\GetEntryBalanceQuery;
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

    public function create(Entry $entity): self
    {
        $this->getEntityManager()->persist($entity);

        return $this;
    }

    public function remove(Entry $entry): self
    {
        $this->getEntityManager()->remove($entry);

        return $this;
    }

    public function balance(GetEntryBalanceQuery $query): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('e')
            ->select('SUM(e.amount) as sum, b.id')
            ->leftJoin('e.budget', 'b')
            ->groupBy('b.id');

        if (!is_null($query->getAccount())) {
            $queryBuilder
                ->andWhere('e.account = :account')
                ->setParameter('account', $query->getAccount());
        }

        return $queryBuilder;
    }

    public function getEntriesQueryBuilder(FindEntriesQuery $searchQuery): QueryBuilder
    {
        $queryBuilder = $this
            ->createQueryBuilder('e')
        ;

        if (!is_null($searchQuery->getStartDate()) && !is_null($searchQuery->getEndDate())) {
            $queryBuilder
                ->andWhere('e.createdAt BETWEEN :startDate AND :endDate')
                ->setParameter('startDate', $searchQuery->getStartDate()->format('Y-m-d'))
                ->setParameter('endDate', $searchQuery->getEndDate()->format('Y-m-d'));
        }

        if (!is_null($searchQuery->getName())) {
            $queryBuilder
                ->andWhere('e.name LIKE :name')
                ->setParameter('name', '%' . $searchQuery->getName() . '%');
        }

        if (EntryTypeEnum::TYPE_SPENT === $searchQuery->getType()) {
            $queryBuilder
                ->andWhere('e.budget IS NULL');
        } elseif (EntryTypeEnum::TYPE_FORECAST === $searchQuery->getType()) {
            $queryBuilder
                ->andWhere('e.budget IS NOT NULL');
        }

        if (!is_null($searchQuery->getAccount())) {
            $queryBuilder
                ->andWhere('e.account = :account')
                ->setParameter('account', $searchQuery->getAccount());
        }

        if (!is_null($searchQuery->getBudget())) {
            $queryBuilder
                ->andWhere('e.budget = :budget')
                ->setParameter('budget', $searchQuery->getBudget());
        }

        $flags = $searchQuery->getFlags();
        if ([] !== $flags) {
            $orConditions = [];
            $paramIndex   = 0;

            foreach ($flags as $flag) {
                if (FindEntriesQuery::WITHOUT_FLAG_VALUE === $flag) {
                    $orConditions[] = 'JSON_LENGTH(e.flags) = 0';
                } else {
                    if (!$flag instanceof EntryFlagEnum) {
                        continue;
                    }

                    $orConditions[] = sprintf("JSON_SEARCH(e.flags, 'one', :flag_%d) IS NOT NULL", $paramIndex);
                    $queryBuilder->setParameter('flag_' . $paramIndex, $flag->value);
                    ++$paramIndex;
                }
            }

            $queryBuilder->andWhere('(' . implode(' OR ', $orConditions) . ')');
        }

        switch ($searchQuery->getOrderBy()) {
            case 'createdAt':
                $queryBuilder->orderBy('e.createdAt', $searchQuery->getOrderDirection()->value);
                break;
            default:
                $queryBuilder->orderBy('e.id', $searchQuery->getOrderDirection()->value);
                break;
        }

        return $queryBuilder;
    }
}
