<?php

namespace App\Domain\Account\Model;

use App\Infrastructure\KnpPaginator\Model\OrderableInterface;
use App\Infrastructure\KnpPaginator\Model\OrderableTrait;

class AccountSearchCommand implements OrderableInterface
{
    use OrderableTrait;

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
