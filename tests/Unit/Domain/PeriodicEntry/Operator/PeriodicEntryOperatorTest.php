<?php

namespace App\Tests\Unit\Domain\PeriodicEntry\Operator;

use App\Domain\Account\Entity\Account;
use App\Domain\Budget\Entity\Budget;
use App\Domain\Entry\Manager\EntryManager;
use App\Domain\PeriodicEntry\Entity\PeriodicEntry;
use App\Domain\PeriodicEntry\Exception\PeriodicEntrySplitBudgetException;
use App\Domain\PeriodicEntry\Manager\PeriodicEntryManager;
use App\Shared\Operator\PeriodicEntryOperator;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class PeriodicEntryOperatorTest extends TestCase
{
    private EntryManager $entryManagerMock;
    private PeriodicEntryManager $periodicEntryManagerMock;

    protected function setUp(): void
    {
        $this->entryManagerMock         = $this->createMock(EntryManager::class);
        $this->periodicEntryManagerMock = $this->createMock(PeriodicEntryManager::class);
    }

    private function generatePeriodicEntryOperator(): PeriodicEntryOperator
    {
        return new PeriodicEntryOperator(
            $this->entryManagerMock,
            $this->periodicEntryManagerMock
        );
    }

    public function testSplitAlreadyDoneMustDoNothing(): void
    {
        $executionDate = new DateTimeImmutable('first day of this month +15 days 14:00:00');
        $periodicEntry = (new PeriodicEntry())
            ->setLastExecutionDate($executionDate)
        ;

        $this->expectException(PeriodicEntrySplitBudgetException::class);
        $this->expectExceptionMessage('A periodic entry has already been executed.');

        $this->entryManagerMock
            ->expects(self::never())
            ->method('create');

        $this->periodicEntryManagerMock
            ->expects(self::never())
            ->method('update');

        $this->generatePeriodicEntryOperator()
            ->addSplitForBudgets($periodicEntry);
    }

    public function testSplitNotYetDoneForSpentMustCreateOnlyOneEntry(): void
    {
        $periodicEntry = (new PeriodicEntry())
            ->setLastExecutionDate(null)
            ->setAmount(200)
            ->setName('Spent')
            ->setAccount(new Account())
        ;

        $this->entryManagerMock
            ->expects(self::once())
            ->method('create');

        $this->periodicEntryManagerMock
            ->expects(self::once())
            ->method('update');

        $this->generatePeriodicEntryOperator()
            ->addSplitForBudgets($periodicEntry);

        self::assertNotNull($periodicEntry->getLastExecutionDate());
    }

    public function testSplitNotYetDoneForForecastMustCreateAllEntries(): void
    {
        $periodicEntry = (new PeriodicEntry())
            ->setLastExecutionDate(null)
            ->setAmount(200)
            ->setName('Spent')
            ->setAccount(new Account())
            ->addBudget((new Budget())
                ->setName('Budget')
                ->setAmount(200)
            )
            ->addBudget((new Budget())
                ->setName('Budget')
                ->setAmount(200)
            )
        ;

        $this->entryManagerMock
            ->expects(self::exactly(2))
            ->method('create');

        $this->periodicEntryManagerMock
            ->expects(self::once())
            ->method('update');

        $this->generatePeriodicEntryOperator()
            ->addSplitForBudgets($periodicEntry);

        self::assertNotNull($periodicEntry->getLastExecutionDate());
    }

    public function testSplitForecastMustCreateAllEntriesExceptDisabledBudgets(): void
    {
        $periodicEntry = (new PeriodicEntry())
            ->setLastExecutionDate(null)
            ->setAmount(200)
            ->setName('Spent')
            ->setAccount(new Account())
            ->addBudget((new Budget())
                ->setName('Budget')
                ->setAmount(200)
            )
            ->addBudget((new Budget())
                ->setName('Budget')
                ->setAmount(200)
            )
            ->addBudget((new Budget())
                ->setEnable(false)
                ->setName('Budget')
                ->setAmount(200)
            )
        ;

        $this->entryManagerMock
            ->expects(self::exactly(2))
            ->method('create');

        $this->periodicEntryManagerMock
            ->expects(self::once())
            ->method('update');

        $this->generatePeriodicEntryOperator()
            ->addSplitForBudgets($periodicEntry);

        self::assertNotNull($periodicEntry->getLastExecutionDate());
    }
}
