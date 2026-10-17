<?php

namespace App\Tests\Unit\Shared;

use App\Domain\Budget\Entity\Budget;

trait BudgetTestTrait
{
    use EntryTestTrait;

    private function generateBudget(array $data = []): Budget
    {
        $budget = (new Budget())
            ->setName('A Budget')
            ->setAmount($data['amount'] ?? 0);

        foreach ($data['entries'] as $entryData) {
            $entry = $this->generateEntry([
                ...$entryData,
                'budget' => $budget,
            ]);

            $budget->addEntry($entry);
        }

        return $budget;
    }
}
