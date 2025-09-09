<?php

namespace App\Domain\Assignment\DTO;

use App\Domain\Account\Entity\Account;
use App\Infrastructure\KnpPaginator\DTO\OrderableInterface;
use App\Infrastructure\KnpPaginator\DTO\OrderableTrait;

class AssignmentSearchCommand implements OrderableInterface
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

    public function setAccount(?Account $account): AssignmentSearchCommand
    {
        $this->account = $account;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): AssignmentSearchCommand
    {
        $this->name = $name;

        return $this;
    }
}
