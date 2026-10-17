<?php

namespace App\Domain\Entry\ValueObject;

readonly class EntryBalance
{
    public function __construct(
        private int $totalSpent,
        private int $totalForecast,
    ) {
    }

    public function getTotalSpent(): int
    {
        return $this->totalSpent;
    }

    public function getTotalForecast(): int
    {
        return $this->totalForecast;
    }
}
