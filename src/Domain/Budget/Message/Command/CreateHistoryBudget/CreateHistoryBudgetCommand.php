<?php

namespace App\Domain\Budget\Message\Command\CreateHistoryBudget;

use App\Domain\Budget\Entity\Budget;
use App\Domain\Budget\Entity\HistoryBudget;
use App\Shared\Cqs\Message\Command\CommandInterface;
use DateTimeImmutable;
use Symfony\Component\ObjectMapper\Attribute\Map;

/**
 * @see CreateHistoryBudgetHandler
 */
#[Map(HistoryBudget::class)]
class CreateHistoryBudgetCommand implements CommandInterface
{
    public function __construct(
        private Budget $budget,
        private int $amount,
        private DateTimeImmutable $date,
        private int $spent,
        private float $relativeProgress,
    ) {
    }

    public function getBudget(): Budget
    {
        return $this->budget;
    }

    public function setBudget(Budget $budget): CreateHistoryBudgetCommand
    {
        $this->budget = $budget;

        return $this;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): CreateHistoryBudgetCommand
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(DateTimeImmutable $date): CreateHistoryBudgetCommand
    {
        $this->date = $date;

        return $this;
    }

    public function getSpent(): int
    {
        return $this->spent;
    }

    public function setSpent(int $spent): CreateHistoryBudgetCommand
    {
        $this->spent = $spent;

        return $this;
    }

    public function getRelativeProgress(): float
    {
        return $this->relativeProgress;
    }

    public function setRelativeProgress(float $relativeProgress): CreateHistoryBudgetCommand
    {
        $this->relativeProgress = $relativeProgress;

        return $this;
    }
}
