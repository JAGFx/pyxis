<?php

namespace App\Shared\Message\Query\GetAmountBalance;

use App\Shared\Cqs\Message\Query\QueryInterface;

/**
 * @see GetAmountBalanceHandler
 */
class GetAmountBalanceQuery implements QueryInterface
{
    public function __construct(
        private ?int $accountId = null,
    ) {
    }

    public function getAccountId(): ?int
    {
        return $this->accountId;
    }

    public function setAccountId(?int $accountId): GetAmountBalanceQuery
    {
        $this->accountId = $accountId;

        return $this;
    }
}
