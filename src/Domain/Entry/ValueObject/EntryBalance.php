<?php

namespace App\Domain\Entry\ValueObject;

readonly class EntryBalance
{
    public function __construct(
        private float $totalSpent,
        private float $totalForecast,
    ) {
    }

    public function getTotalSpent(): float
    {
        return $this->totalSpent;
    }

    public function getTotalForecast(): float
    {
        return $this->totalForecast;
    }
}
