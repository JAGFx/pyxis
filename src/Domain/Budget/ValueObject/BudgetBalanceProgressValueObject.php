<?php

namespace App\Domain\Budget\ValueObject;

use App\Domain\Budget\Entity\BudgetStatusEnum;

readonly class BudgetBalanceProgressValueObject
{
    public function __construct(
        private ?string $name = null,
        private ?float $progress = null,
        private ?BudgetStatusEnum $status = null,
        private ?float $amount = null,
        private ?float $trueRelativeProgress = null,
    ) {
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getProgress(): ?float
    {
        return $this->progress;
    }

    public function getStatus(): ?BudgetStatusEnum
    {
        return $this->status;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function getTrueRelativeProgress(): ?float
    {
        return $this->trueRelativeProgress;
    }

    public function getBalance(): float
    {
        return $this->amount - $this->progress;
    }

    public function isOver(): bool
    {
        return BudgetStatusEnum::STATUS_IS_OVER === $this->status;
    }
}
