<?php

namespace App\Shared\ValueObject;

readonly class AmountBalance
{
    public function __construct(
        private int $total,
        private int $totalSpent,
        private int $totalForecast,
        private int $assignments,
        private ?string $accountName = null,
    ) {
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getTotalSpent(): int
    {
        return $this->totalSpent;
    }

    public function getTotalForecast(): int
    {
        return $this->totalForecast;
    }

    public function getAssignments(): int
    {
        return $this->assignments;
    }

    public function getAccountName(): ?string
    {
        return $this->accountName;
    }
}
