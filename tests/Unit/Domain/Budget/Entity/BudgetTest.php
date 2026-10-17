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
            'amount'  => 1000.0,
            'entries' => [
                ['entryAmount' => -300.0],
                ['entryAmount' => -150.0],
            ],
        ]);

        self::assertSame(-450.0, $budget->getProgress());
        self::assertSame(450.0, $budget->getProgress(true));
        self::assertSame(45.0, $budget->getRelativeProgress(true));
    }
}
