<?php

namespace App\Domain\Assignment\Message\Query\GetAssignmentBalance;

use App\Shared\Cqs\Message\Query\QueryInterface;

/**
 * @see GetAssignmentBalanceHandler
 */
class GetAssignmentBalanceQuery implements QueryInterface
{
    public function __construct(
        private ?int $accountId = null,
    ) {
    }

    public function getAccountId(): ?int
    {
        return $this->accountId;
    }

    public function setAccountId(?int $accountId): GetAssignmentBalanceQuery
    {
        $this->accountId = $accountId;

        return $this;
    }
}
