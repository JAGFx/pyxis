<?php

namespace App\Tests\Unit\Domain\PeriodicEntry\Entity;

use App\Domain\Budget\Entity\Budget;
use App\Domain\Entry\Entity\EntryTypeEnum;
use App\Domain\PeriodicEntry\Entity\PeriodicEntry;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class PeriodicEntryTest extends TestCase
{
    public function testAPeriodicEntryWithBudgetMustReturnForecastType(): void
    {
        $periodicEntry = new PeriodicEntry();
        $budget        = (new Budget());

        $periodicEntry->addBudget($budget);

        self::assertSame(EntryTypeEnum::TYPE_FORECAST, $periodicEntry->getType());
        self::assertTrue($periodicEntry->isForecast());
    }

    public function testAPeriodicEntryWithoutBudgetsMustReturnSpentType(): void
    {
        $periodicEntry = (new PeriodicEntry())
            ->setAmount(rand(1, 100));

        self::assertSame(EntryTypeEnum::TYPE_SPENT, $periodicEntry->getType());
        self::assertTrue($periodicEntry->isSpent());
    }

    public function testTotalAmountForSpentMustReturnSelfAmount(): void
    {
        $periodicEntry = new PeriodicEntry()
            ->setAmount(250000)
        ;

        self::assertSame(250000, $periodicEntry->getTotalAmount());
    }

    public function testTotalAmountWithOnlyEnabledBudget(): void
    {
        $periodicEntry = new PeriodicEntry()
            ->setAmount(250000)
            ->setBudgets(new ArrayCollection([
                new Budget()->setEnabled(true)->setAmount(10000),
                new Budget()->setEnabled(true)->setAmount(20000),
                new Budget()->setEnabled(true)->setAmount(30000),
            ]))
        ;

        // 833 + 1666 + 2500: base monthly shares, the remainders are added on the first months only
        self::assertSame(4999, $periodicEntry->getTotalAmount());
    }

    public function testTotalAmountWithDisabledBudgetMustBeExclude(): void
    {
        $periodicEntry = new PeriodicEntry()
            ->setAmount(250000)
            ->setBudgets(new ArrayCollection([
                new Budget()->setEnabled(true)->setAmount(10000),
                new Budget()->setEnabled(true)->setAmount(20000),
                new Budget()->setEnabled(false)->setAmount(30000),
            ]))
        ;

        self::assertSame(2499, $periodicEntry->getTotalAmount());
    }

    public function testGetAmountForBudgetEnabled(): void
    {
        $targetBudget = new Budget()->setEnabled(true)->setAmount(120000);

        $periodicEntry = new PeriodicEntry()
            ->setAmount(250000)
            ->setBudgets(new ArrayCollection([
                new Budget()->setEnabled(true)->setAmount(10000),
                new Budget()->setEnabled(true)->setAmount(20000),
                $targetBudget,
            ]))
        ;

        self::assertSame(10000, $periodicEntry->getAmountFor($targetBudget)); // 1200 / 12
    }

    public function testGetAmountForBudgetDisabledMustReturnZero(): void
    {
        $targetBudget = new Budget()->setEnabled(false)->setAmount(30000);

        $periodicEntry = new PeriodicEntry()
            ->setAmount(250000)
            ->setBudgets(new ArrayCollection([
                new Budget()->setEnabled(true)->setAmount(10000),
                new Budget()->setEnabled(true)->setAmount(20000),
                $targetBudget,
            ]))
        ;

        self::assertSame(0, $periodicEntry->getAmountFor($targetBudget));
    }

    public function testMonthlySharesSumUpToTheBudgetAmount(): void
    {
        $budget        = new Budget()->setEnabled(true)->setAmount(10000);
        $periodicEntry = new PeriodicEntry()->setBudgets(new ArrayCollection([$budget]));

        $shares = array_map(fn (int $month): int => $periodicEntry->getAmountFor($budget, $month), range(1, 12));

        // 10000 = 833 * 12 + 4: one more cent from January to April
        self::assertSame([834, 834, 834, 834, 833, 833, 833, 833, 833, 833, 833, 833], $shares);
        self::assertSame(10000, array_sum($shares));
    }
}
