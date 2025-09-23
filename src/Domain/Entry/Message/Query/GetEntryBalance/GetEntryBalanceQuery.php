<?php

namespace App\Domain\Entry\Message\Query\GetEntryBalance;

use App\Shared\Cqs\Message\Query\QueryInterface;

/**
 * @see GetEntryBalanceHandler
 */
class GetEntryBalanceQuery implements QueryInterface
{
    public function __construct(
        private ?int $accountId = null,
    ) {
    }

    public function getAccountId(): ?int
    {
        return $this->accountId;
    }

    public function setAccountId(?int $accountId): GetEntryBalanceQuery
    {
        $this->accountId = $accountId;

        return $this;
    }
}
