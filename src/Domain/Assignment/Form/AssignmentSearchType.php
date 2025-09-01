<?php

namespace App\Domain\Assignment\Form;

use App\Domain\Account\Entity\Account;

class AssignmentSearchType
{
    public function __construct(
        private ?Account $account = null,
    ) {
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): AssignmentSearchType
    {
        $this->account = $account;

        return $this;
    }
}
