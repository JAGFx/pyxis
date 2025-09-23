<?php

namespace App\Domain\Account\Message\Query\FindAccounts;

use App\Domain\Account\Entity\Account;
use App\Domain\Account\Repository\AccountRepository;
use App\Shared\Cqs\Handler\QueryHandlerInterface;

/**
 * @see FindAccountsQuery
 */
readonly class FindAccountsHandler implements QueryHandlerInterface
{
    public function __construct(
        private AccountRepository $repository,
    ) {
    }

    /**
     * @return Account[]
     */
    public function __invoke(FindAccountsQuery $query): array
    {
        /** @var Account[] $accounts */
        $accounts = $this->repository
            ->getAccountsQueryBuilder($query)
            ->getQuery()
            ->getResult()
        ;

        return $accounts;
    }
}
