<?php

namespace App\Shared\Message\Command\GenerateHistoryBudgetsForYear;

use App\Shared\Cqs\Message\Command\CommandInterface;

/**
 * @see GenerateHistoryBudgetsForYearHandler
 */
class GenerateHistoryBudgetsForYearCommand implements CommandInterface
{
    public function __construct(
        private int $year,
    ) {
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function setYear(int $year): GenerateHistoryBudgetsForYearCommand
    {
        $this->year = $year;

        return $this;
    }
}
