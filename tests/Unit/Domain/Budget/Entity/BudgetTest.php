<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Budget\Entity;

use App\Tests\Unit\Shared\BudgetTestTrait;
use PHPUnit\Framework\TestCase;

class BudgetTest extends TestCase
{
    use BudgetTestTrait;

    public function testProgressAsSpentOnlyIsTheAbsoluteProgress(): void
    {
        $budget = $this->generateBudget([
            'amount'  => 100000,
            'entries' => [
                ['entryAmount' => -30000],
                ['entryAmount' => -15000],
            ],
        ]);

        self::assertSame(-45000, $budget->getProgress());
        self::assertSame(45000, $budget->getProgress(true));
        self::assertSame(45.0, $budget->getRelativeProgress(true));
    }
}
