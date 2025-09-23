<?php

namespace App\Domain\Entry\Message\Query\GetEntryBalance;

use App\Domain\Account\Entity\Account;
use App\Shared\Cqs\Message\Query\QueryInterface;

/**
 * @see GetEntryBalanceHandler
 */
class GetEntryBalanceQuery implements QueryInterface
{
    public function __construct(
        private ?Account $account = null,
    ) {
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): GetEntryBalanceQuery
    {
        $this->account = $account;

        return $this;
    }
}
