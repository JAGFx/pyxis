<?php

namespace App\Domain\Assignment\Message\Query\FindAssignments;

use App\Infrastructure\KnpPaginator\DTO\OrderableInterface;
use App\Infrastructure\KnpPaginator\DTO\OrderableTrait;
use App\Shared\Cqs\Message\Query\QueryInterface;

/**
 * @see FindAssignmentsHandler
 */
class FindAssignmentsQuery implements OrderableInterface, QueryInterface
{
    use OrderableTrait;

    public function __construct(
        private ?int $accountId = null,
        private ?string $name = null,
    ) {
    }

    public function getAccountId(): ?int
    {
        return $this->accountId;
    }

    public function setAccountId(?int $accountId): FindAssignmentsQuery
    {
        $this->accountId = $accountId;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): FindAssignmentsQuery
    {
        $this->name = $name;

        return $this;
    }
}
