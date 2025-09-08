<?php

namespace App\Domain\Account\Manager;

use App\Domain\Account\DTO\AccountSearchCommand;
use App\Domain\Account\Entity\Account;
use App\Domain\Account\Repository\AccountRepository;

class AccountManager
{
    public function __construct(
        private readonly AccountRepository $repository,
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

    public function create(Account $account): void
    {
        $this->repository
            ->create($account)
            ->flush();
    }

    public function update(): void
    {
        $this->repository->flush();
    }
}
