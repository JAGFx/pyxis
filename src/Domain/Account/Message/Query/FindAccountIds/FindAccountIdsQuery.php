<?php

namespace App\Domain\Account\Message\Query\FindAccountIds;

use App\Shared\Cqs\Message\Query\QueryInterface;

/**
 * @see FindAccountIdsHandler
 */
class FindAccountIdsQuery implements QueryInterface
{
    public function __construct(
        private ?int $maxResults = null,
    ) {
    }

    public function getMaxResults(): ?int
    {
        return $this->maxResults;
    }

    public function setMaxResults(?int $maxResults): FindAccountIdsQuery
    {
        $this->maxResults = $maxResults;

        return $this;
    }
}
