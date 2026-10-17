<?php

namespace App\Domain\Budget\Model;

use App\Domain\Budget\Entity\BudgetStatusEnum;

trait BudgetProgressTrait
{
    abstract public function getProgress(): int;

    abstract public function getAmount(): int;

    public function getRelativeProgress(bool $showAsSpentOnly = false): float
    {
        if (0 === $this->getAmount()) {
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
