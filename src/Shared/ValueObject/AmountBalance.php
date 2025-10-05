<?php

namespace App\Shared\ValueObject;

readonly class AmountBalance
{
    public function __construct(
        private float $total,
        private float $totalSpent,
        private float $totalForecast,
        private float $assignments,
        private ?string $accountName = null,
    ) {
    }

    public function getTotal(): float
    {
        return $this->total;
    }

    public function getTotalSpent(): float
    {
        return $this->totalSpent;
    }

    public function getTotalForecast(): float
    {
        return $this->totalForecast;
    }

    public function getAssignments(): float
    {
        return $this->assignments;
    }

    public function getAccountName(): ?string
    {
        return $this->accountName;
    }
}
