<?php

namespace App\Domain\Account\Manager;

use App\Domain\Account\DTO\AccountSearchCommand;
use App\Domain\Account\Entity\Account;
use App\Domain\Account\Repository\AccountRepository;
use Doctrine\ORM\EntityManagerInterface;

class AccountManager
{
    public function __construct(
        private readonly AccountRepository $repository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return Account[]
     */
    public function getAccounts(?AccountSearchCommand $command = null): array
    {
        /** @var Account[] $accounts */
        $accounts = $this->repository
            ->getAccountsQueryBuilder($command ?? new AccountSearchCommand())
            ->getQuery()
            ->getResult()
        ;

        return $accounts;
    }

    public function toggle(Account $account): void
    {
        $account->setEnable(!$account->isEnable());

        $this->update();
    }

    public function create(Account $entity, bool $flush = true): void
    {
        $this->repository->create($entity);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function update(bool $flush = true): void
    {
        if ($flush) {
            $this->entityManager->flush();
        }
    }
}
