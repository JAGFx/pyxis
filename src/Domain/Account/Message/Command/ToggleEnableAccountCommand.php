<?php

namespace App\Domain\Account\Message\Command;

use App\Domain\Account\Entity\Account;
use App\Shared\Cqs\Message\Command\CommandInterface;

class ToggleEnableAccountCommand implements CommandInterface
{
    public function __construct(
        private Account $account,
    ) {
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): ToggleEnableAccountCommand
    {
        $this->account = $account;

        return $this;
    }
}
