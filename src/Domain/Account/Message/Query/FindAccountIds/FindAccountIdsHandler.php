<?php

namespace App\Domain\Account\Message\Query\FindAccountIds;

use App\Domain\Account\Repository\AccountRepository;
use App\Domain\Account\ValueObject\AccountIds;
use App\Shared\Cqs\Handler\QueryHandlerInterface;

/**
 * @see FindAccountIdsQuery
 */
readonly class FindAccountIdsHandler implements QueryHandlerInterface
{
    public function __construct(
        private AccountRepository $accountRepository,
    ) {
    }

    public function __invoke(FindAccountIdsQuery $query): AccountIds
    {
        $accountIds = $this->accountRepository->getAccountIds($query);
        $totalCount = $this->accountRepository->getTotalAccount();

        return new AccountIds($totalCount, $accountIds);
    }
}
