<?php

namespace App\Domain\Account\Message\Query\FindAccounts;

use App\Domain\Budget\Entity\Budget;
use App\Infrastructure\KnpPaginator\DTO\OrderableInterface;
use App\Infrastructure\KnpPaginator\DTO\OrderableTrait;
use App\Shared\Cqs\Message\Query\QueryInterface;

/**
 * @see FindAccountsHandler
 */
class FindAccountsQuery implements OrderableInterface, QueryInterface
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

    public function setEnabled(?bool $enabled): FindAccountsQuery
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): FindAccountsQuery
    {
        $this->name = $name;

        return $this;
    }

    public function getBudget(): ?Budget
    {
        return $this->budget;
    }

    public function setBudget(?Budget $budget): FindAccountsQuery
    {
        $this->budget = $budget;

        return $this;
    }

    public function hasPositiveOrNegativeBalance(): ?bool
    {
        return $this->positiveOrNegativeBalance;
    }

    public function setPositiveOrNegativeBalance(?bool $positiveOrNegativeBalance): FindAccountsQuery
    {
        $this->positiveOrNegativeBalance = $positiveOrNegativeBalance;

        return $this;
    }
}
