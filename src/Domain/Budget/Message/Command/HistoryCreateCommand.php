<?php

namespace App\Domain\Budget\Message\Command;

use App\Domain\Budget\Entity\Budget;
use App\Domain\Budget\Entity\HistoryBudget;
use App\Shared\Cqs\Message\Command\CommandInterface;
use DateTimeImmutable;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(HistoryBudget::class)]
class HistoryCreateCommand implements CommandInterface
{
    public function __construct(
        private Budget $budget,
        private float $amount,
        private DateTimeImmutable $date,
        private float $spent,
        private float $relativeProgress,
    ) {
    }

    public function getBudget(): Budget
    {
        return $this->budget;
    }

    public function setBudget(Budget $budget): HistoryCreateCommand
    {
        $this->budget = $budget;

        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): HistoryCreateCommand
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(DateTimeImmutable $date): HistoryCreateCommand
    {
        $this->date = $date;

        return $this;
    }

    public function getSpent(): float
    {
        return $this->spent;
    }

    public function setSpent(float $spent): HistoryCreateCommand
    {
        $this->spent = $spent;

        return $this;
    }

    public function getRelativeProgress(): float
    {
        return $this->relativeProgress;
    }

    public function setRelativeProgress(float $relativeProgress): HistoryCreateCommand
    {
        $this->relativeProgress = $relativeProgress;

        return $this;
    }
}
