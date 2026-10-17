<?php

namespace App\Domain\Budget\ValueObject;

use App\Domain\Budget\Model\BudgetProgressTrait;

final readonly class BudgetValueObject
{
    use BudgetProgressTrait;

    /**
     * In cents.
     */
    private int $amount;

    /**
     * In cents.
     */
    private int $progress;

    /**
     * Built by DQL (NEW ... SUM(e.amount)): the SUM of an integer column is returned as a numeric string.
     */
    public function __construct(
        private int $id,
        private string $name,
        int|string $amount,
        private bool $enable,
        int|string $progress,
    ) {
        $this->amount   = (int) $amount;
        $this->progress = (int) $progress;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function isEnable(): bool
    {
        return $this->enable;
    }

    public function getProgress(bool $showAsSpentOnly = false): int
    {
        return ($showAsSpentOnly)
            ? abs($this->progress)
            : $this->progress;
    }
}
