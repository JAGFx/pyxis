<?php

namespace App\Domain\Account\Request;

use App\Domain\Budget\Entity\Budget;
use App\Infrastructure\KnpPaginator\DTO\OrderableInterface;
use App\Infrastructure\KnpPaginator\DTO\OrderableTrait;

class AccountSearchRequest implements OrderableInterface
{
    use OrderableTrait;

    public function __construct(
        private ?bool $enabled = true,
        private ?string $name = null,
        private ?Budget $budget = null,
        private ?bool $positiveOrNegativeBalance = null,
    ) {
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(?bool $enabled): AccountSearchRequest
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): AccountSearchRequest
    {
        $this->name = $name;

        return $this;
    }

    public function getBudget(): ?Budget
    {
        return $this->budget;
    }

    public function setBudget(?Budget $budget): AccountSearchRequest
    {
        $this->budget = $budget;

        return $this;
    }

    public function hasPositiveOrNegativeBalance(): ?bool
    {
        return $this->positiveOrNegativeBalance;
    }

    public function setPositiveOrNegativeBalance(?bool $positiveOrNegativeBalance): AccountSearchRequest
    {
        $this->positiveOrNegativeBalance = $positiveOrNegativeBalance;

        return $this;
    }
}
