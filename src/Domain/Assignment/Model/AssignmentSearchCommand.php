<?php

namespace App\Domain\Assignment\Model;

use App\Domain\Account\Entity\Account;
use App\Infrastructure\KnpPaginator\Model\OrderableInterface;
use App\Infrastructure\KnpPaginator\Model\OrderableTrait;

class AssignmentSearchCommand implements OrderableInterface
{
    use OrderableTrait;

    public function __construct(
        private ?Account $account = null,
        private ?bool $enable = true,
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

    public function getEnable(): ?bool
    {
        return $this->enable;
    }

    public function setEnable(?bool $enable): AssignmentSearchCommand
    {
        $this->enable = $enable;

        return $this;
    }
}
