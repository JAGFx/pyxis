<?php

namespace App\Domain\Account\Repository;

use App\Domain\Account\Entity\Account;
use App\Domain\Account\Message\Query\FindAccounts\FindAccountsQuery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Account>
 */
class AccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Account::class);
    }

    public function getAccountsQueryBuilder(FindAccountsQuery $searchQuery): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('a');
        if (null !== $searchQuery->isEnabled()) {
            $queryBuilder
                ->andWhere('a.enabled = :enable')
                ->setParameter('enable', $searchQuery->isEnabled());
        }

        if (null !== $searchQuery->getName()) {
            $queryBuilder
                ->andWhere('a.name LIKE :name')
                ->setParameter('name', '%' . $searchQuery->getName() . '%');
        }

        if (true === $searchQuery->hasPositiveOrNegativeBalance()) {
            $queryBuilder = $queryBuilder
                ->leftJoin('a.entries', 'e');

            if (!is_null($searchQuery->getBudget())) {
                $queryBuilder
                    ->andWhere('e.budget = :budget')
                    ->setParameter('budget', $searchQuery->getBudget());
            }

            $queryBuilder
                ->groupBy('a.id')
                ->having('SUM(e.amount) != 0');
        }

        switch ($searchQuery->getOrderBy()) {
            case 'name':
                $queryBuilder->orderBy('a.name', $searchQuery->getOrderDirection()->value);
                break;
            default:
                $queryBuilder->orderBy('a.id', $searchQuery->getOrderDirection()->value);
                break;
        }

        return $queryBuilder;
    }

    public function create(Account $account): self
    {
        $this->getEntityManager()->persist($account);

        return $this;
    }
}
