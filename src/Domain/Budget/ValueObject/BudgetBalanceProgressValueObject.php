<?php

namespace App\Domain\Budget\ValueObject;

use App\Domain\Budget\Entity\BudgetStatusEnum;

readonly class BudgetBalanceProgressValueObject
{
    public function __construct(
        private ?string $name = null,
        private ?int $progress = null,
        private ?BudgetStatusEnum $status = null,
        private ?int $amount = null,
        private ?float $trueRelativeProgress = null,
    ) {
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getProgress(): ?int
    {
        return $this->progress;
    }

    public function getStatus(): ?BudgetStatusEnum
    {
        return $this->status;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function getTrueRelativeProgress(): ?float
    {
        return $this->trueRelativeProgress;
    }

    public function getBalance(): int
    {
        return $this->amount - $this->progress;
    }

    public function isOver(): bool
    {
        return BudgetStatusEnum::STATUS_IS_OVER === $this->status;
    }
}
