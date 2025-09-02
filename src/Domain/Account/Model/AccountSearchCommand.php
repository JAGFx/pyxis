<?php

namespace App\Domain\Account\Model;

class AccountSearchCommand
{
    public function __construct(
        private ?bool $enable = true,
    ) {
    }

    public function getEnable(): ?bool
    {
        return $this->enable;
    }

    public function setEnable(?bool $enable): AccountSearchCommand
    {
        $this->enable = $enable;

        return $this;
    }
}
