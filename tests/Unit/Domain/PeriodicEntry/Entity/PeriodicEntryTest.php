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
            ->setAmount(2500)
        ;

        self::assertSame(2500.0, $periodicEntry->getTotalAmount());
    }

    public function testTotalAmountWithOnlyEnabledBudget(): void
    {
        $periodicEntry = new PeriodicEntry()
            ->setAmount(2500)
            ->setBudgets(new ArrayCollection([
                new Budget()->setEnable(true)->setAmount(100.0),
                new Budget()->setEnable(true)->setAmount(200.0),
                new Budget()->setEnable(true)->setAmount(300.0),
            ]))
        ;

        self::assertSame(50.0, $periodicEntry->getTotalAmount());
    }

    public function testTotalAmountWithDisabledBudgetMustBeExclude(): void
    {
        $periodicEntry = new PeriodicEntry()
            ->setAmount(2500)
            ->setBudgets(new ArrayCollection([
                new Budget()->setEnable(true)->setAmount(100.0),
                new Budget()->setEnable(true)->setAmount(200.0),
                new Budget()->setEnable(false)->setAmount(300.0),
            ]))
        ;

        self::assertSame(25.0, $periodicEntry->getTotalAmount());
    }

    public function testGetAmountForBudgetEnabled(): void
    {
        $targetBudget = new Budget()->setEnable(true)->setAmount(1200.0);

        $periodicEntry = new PeriodicEntry()
            ->setAmount(2500)
            ->setBudgets(new ArrayCollection([
                new Budget()->setEnable(true)->setAmount(100.0),
                new Budget()->setEnable(true)->setAmount(200.0),
                $targetBudget,
            ]))
        ;

        self::assertSame(100.0, $periodicEntry->getAmountFor($targetBudget)); // 1200 / 12
    }

    public function testGetAmountForBudgetDisabledMustReturnZero(): void
    {
        $targetBudget = new Budget()->setEnable(false)->setAmount(300.0);

        $periodicEntry = new PeriodicEntry()
            ->setAmount(2500)
            ->setBudgets(new ArrayCollection([
                new Budget()->setEnable(true)->setAmount(100.0),
                new Budget()->setEnable(true)->setAmount(200.0),
                $targetBudget,
            ]))
        ;

        self::assertSame(0.0, $periodicEntry->getAmountFor($targetBudget));
    }
}
