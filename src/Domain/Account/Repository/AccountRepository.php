<?php

namespace App\Domain\Account\Repository;

use App\Domain\Account\Entity\Account;
use App\Domain\Account\Request\AccountSearchRequest;
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

    public function getAccountsQueryBuilder(AccountSearchRequest $searchRequest): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('a');
        if (null !== $searchRequest->isEnabled()) {
            $queryBuilder
                ->andWhere('a.enabled = :enable')
                ->setParameter('enable', $searchRequest->isEnabled());
        }

        if (null !== $searchRequest->getName()) {
            $queryBuilder
                ->andWhere('a.name LIKE :name')
                ->setParameter('name', '%' . $searchRequest->getName() . '%');
        }

        if (true === $searchRequest->hasPositiveOrNegativeBalance()) {
            $queryBuilder = $queryBuilder
                ->leftJoin('a.entries', 'e');

            if (!is_null($searchRequest->getBudget())) {
                $queryBuilder
                    ->andWhere('e.budget = :budget')
                    ->setParameter('budget', $searchRequest->getBudget());
            }

            $queryBuilder
                ->groupBy('a.id')
                ->having('SUM(e.amount) != 0');
        }

        switch ($searchRequest->getOrderBy()) {
            case 'name':
                $queryBuilder->orderBy('a.name', $searchRequest->getOrderDirection()->value);
                break;
            default:
                $queryBuilder->orderBy('a.id', $searchRequest->getOrderDirection()->value);
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
