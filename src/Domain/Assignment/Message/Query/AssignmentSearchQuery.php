<?php

namespace App\Domain\Assignment\Message\Query;

use App\Domain\Account\Entity\Account;
use App\Infrastructure\KnpPaginator\DTO\OrderableInterface;
use App\Infrastructure\KnpPaginator\DTO\OrderableTrait;
use App\Shared\Cqs\Message\Query\QueryInterface;

class AssignmentSearchQuery implements OrderableInterface, QueryInterface
{
    use OrderableTrait;

    public function __construct(
        private ?Account $account = null,
        private ?string $name = null,
    ) {
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): AssignmentSearchQuery
    {
        $this->account = $account;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): AssignmentSearchQuery
    {
        $this->name = $name;

        return $this;
    }
}
