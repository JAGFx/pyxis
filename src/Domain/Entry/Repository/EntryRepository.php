<?php

namespace App\Domain\Entry\Repository;

use App\Domain\Entry\Entity\Entry;
use App\Domain\Entry\Entity\EntryFlagEnum;
use App\Domain\Entry\Entity\EntryTypeEnum;
use App\Domain\Entry\Request\EntrySearchRequest;
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

    public function balance(EntrySearchRequest $searchRequest): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('e')
            ->select('SUM(e.amount) as sum, b.id')
            ->leftJoin('e.budget', 'b')
            ->groupBy('b.id');

        if (!is_null($searchRequest->getAccount())) {
            $queryBuilder
                ->andWhere('e.account = :account')
                ->setParameter('account', $searchRequest->getAccount());
        }

        return $queryBuilder;
    }

    public function getEntriesQueryBuilder(EntrySearchRequest $searchRequest): QueryBuilder
    {
        $queryBuilder = $this
            ->createQueryBuilder('e')
        ;

        if (!is_null($searchRequest->getStartDate()) && !is_null($searchRequest->getEndDate())) {
            $queryBuilder
                ->andWhere('e.createdAt BETWEEN :startDate AND :endDate')
                ->setParameter('startDate', $searchRequest->getStartDate()->format('Y-m-d'))
                ->setParameter('endDate', $searchRequest->getEndDate()->format('Y-m-d'));
        }

        if (!is_null($searchRequest->getName())) {
            $queryBuilder
                ->andWhere('e.name LIKE :name')
                ->setParameter('name', '%' . $searchRequest->getName() . '%');
        }

        if (EntryTypeEnum::TYPE_SPENT === $searchRequest->getType()) {
            $queryBuilder
                ->andWhere('e.budget IS NULL');
        } elseif (EntryTypeEnum::TYPE_FORECAST === $searchRequest->getType()) {
            $queryBuilder
                ->andWhere('e.budget IS NOT NULL');
        }

        if (!is_null($searchRequest->getAccount())) {
            $queryBuilder
                ->andWhere('e.account = :account')
                ->setParameter('account', $searchRequest->getAccount());
        }

        if (!is_null($searchRequest->getBudget())) {
            $queryBuilder
                ->andWhere('e.budget = :budget')
                ->setParameter('budget', $searchRequest->getBudget());
        }

        $flags = $searchRequest->getFlags();
        if ([] !== $flags) {
            $orConditions = [];
            $paramIndex   = 0;

            foreach ($flags as $flag) {
                if (EntrySearchRequest::WITHOUT_FLAG_VALUE === $flag) {
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

        switch ($searchRequest->getOrderBy()) {
            case 'createdAt':
                $queryBuilder->orderBy('e.createdAt', $searchRequest->getOrderDirection()->value);
                break;
            default:
                $queryBuilder->orderBy('e.id', $searchRequest->getOrderDirection()->value);
                break;
        }

        return $queryBuilder;
    }
}
