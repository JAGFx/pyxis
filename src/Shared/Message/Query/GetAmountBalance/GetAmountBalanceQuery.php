<?php

namespace App\Shared\Message\Query\GetAmountBalance;

use App\Shared\Cqs\Message\Query\QueryInterface;

/**
 * @see GetAmountBalanceHandler
 */
class GetAmountBalanceQuery implements QueryInterface
{
    public function __construct(
        /** @var array<int|null> */
        private array $accountsId = [],
    ) {
    }

    /**
     * @return array<int|null>
     */
    public function getAccountsId(): array
    {
        return $this->accountsId;
    }

    /**
     * @param array<int|null> $accountsId
     */
    public function setAccountsId(array $accountsId): GetAmountBalanceQuery
    {
        $this->accountsId = $accountsId;

        return $this;
    }
}
