<?php

namespace App\Domain\Account\Manager;

use App\Domain\Account\Entity\Account;
use App\Domain\Account\Message\Command\AccountCreateOrUpdateCommand;
use App\Domain\Account\Message\Query\AccountSearchQuery;
use App\Domain\Account\Repository\AccountRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

readonly class AccountManager
{
    public function __construct(
        private AccountRepository $repository,
        private EntityManagerInterface $entityManager,
        private ObjectMapperInterface $objectMapper,
    ) {
    }

    /**
     * @return Account[]
     */
    public function getAccounts(?AccountSearchQuery $searchQuery = null): array
    {
        /** @var Account[] $accounts */
        $accounts = $this->repository
            ->getAccountsQueryBuilder($searchQuery ?? new AccountSearchQuery())
            ->getQuery()
            ->getResult()
        ;

        return $accounts;
    }

    public function toggle(Account $account): void
    {
        $account->setEnabled(!$account->isEnabled());

        $this->entityManager->flush();
    }

    public function create(AccountCreateOrUpdateCommand $command, bool $flush = true): void
    {
        /** @var Account $account */
        $account = $this->objectMapper->map($command, Account::class);

        $this->repository->create($account);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function update(AccountCreateOrUpdateCommand $command, bool $flush = true): void
    {
        $this->objectMapper->map($command, $command->getOrigin());

        if ($flush) {
            $this->entityManager->flush();
        }
    }
}
