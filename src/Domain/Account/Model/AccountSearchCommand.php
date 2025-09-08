<?php

namespace App\Domain\Account\Model;

use App\Infrastructure\KnpPaginator\Model\OrderableInterface;
use App\Infrastructure\KnpPaginator\Model\OrderableTrait;

class AccountSearchCommand implements OrderableInterface
{
    use OrderableTrait;

    public function __construct(
        private ?bool $enable = true,
        private ?string $name = null,
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): AccountSearchCommand
    {
        $this->name = $name;
        return $this;
    }
}
