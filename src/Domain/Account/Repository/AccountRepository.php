<?php

namespace App\Domain\Account\Repository;

use App\Domain\Account\DTO\AccountSearchCommand;
use App\Domain\Account\Entity\Account;
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

    public function getAccountsQueryBuilder(AccountSearchCommand $command): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('a');
        if (null !== $command->getEnable()) {
            $queryBuilder
                ->andWhere('a.enable = :enable')
                ->setParameter('enable', $command->getEnable());
        }

        if (null !== $command->getName()) {
            $queryBuilder
                ->andWhere('a.name LIKE :name')
                ->setParameter('name', '%' . $command->getName() . '%');
        }

        switch ($command->getOrderBy()) {
            case 'name':
                $queryBuilder->orderBy('a.name', $command->getOrderDirection()->value);
                break;
            default:
                $queryBuilder->orderBy('a.id', $command->getOrderDirection()->value);
                break;
        }

        return $queryBuilder;
    }

    public function create(Account $account): self
    {
        $this->_em->persist($account);

        return $this;
    }

    public function flush(): void
    {
        $this->_em->flush();
    }
}
