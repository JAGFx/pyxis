<?php

namespace App\Domain\Budget\Model;

use App\Domain\Budget\Entity\BudgetStatusEnum;

trait BudgetProgressTrait
{
    abstract public function getProgress(): float;

    abstract public function getAmount(): float;

    public function getRelativeProgress(bool $showAsSpentOnly = false): float
    {
        if (0.0 === $this->getAmount()) {
            return 0.0;
        }

        return $this->getProgress($showAsSpentOnly) * 100 / $this->getAmount();
    }

    public function getStatus(bool $showAsSpentOnly = false): BudgetStatusEnum
    {
        $relativeProgress = $this->getRelativeProgress($showAsSpentOnly);

        return BudgetStatusEnum::statusByProgress($relativeProgress);
    }
}
