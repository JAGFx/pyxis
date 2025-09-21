<?php

namespace App\Domain\Assignment\Message\Query;

use App\Domain\Account\Entity\Account;
use App\Shared\Cqs\Message\Query\QueryInterface;

class GetAssignmentBalanceQuery implements QueryInterface
{
    public function __construct(
        private ?Account $account = null,
    ) {
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): GetAssignmentBalanceQuery
    {
        $this->account = $account;

        return $this;
    }
}
