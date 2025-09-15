<?php

namespace App\Domain\Account\Manager;

use App\Domain\Account\Entity\Account;
use App\Domain\Account\Repository\AccountRepository;
use App\Domain\Account\Request\AccountSearchRequest;
use Doctrine\ORM\EntityManagerInterface;

readonly class AccountManager
{
    public function __construct(
        private AccountRepository $repository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return Account[]
     */
    public function getAccounts(?AccountSearchRequest $searchRequest = null): array
    {
        /** @var Account[] $accounts */
        $accounts = $this->repository
            ->getAccountsQueryBuilder($searchRequest ?? new AccountSearchRequest())
            ->getQuery()
            ->getResult()
        ;

        return $accounts;
    }

    public function toggle(Account $account): void
    {
        $account->setEnabled(!$account->isEnabled());

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
