<?php

namespace App\Tests\Unit\Shared\Operator;

use App\Domain\Account\Entity\Account;
use App\Domain\Budget\Entity\Budget;
use App\Domain\PeriodicEntry\Entity\PeriodicEntry;
use App\Domain\PeriodicEntry\Exception\PeriodicEntrySplitBudgetException;
use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Shared\Operator\PeriodicEntryOperator;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class PeriodicEntryOperatorTest extends TestCase
{
    private EntityManagerInterface $entityManagerMock;
    private MessageBus $messageBusMock;

    protected function setUp(): void
    {
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->messageBusMock    = $this->createMock(MessageBus::class);
    }

    private function generatePeriodicEntryOperator(): PeriodicEntryOperator
    {
        return new PeriodicEntryOperator(
            $this->entityManagerMock,
            $this->messageBusMock,
        );
    }

    public function testAddSplitForBudgetsThrowsExceptionWhenNotScheduledForToday(): void
    {
        $tomorrow      = new DateTimeImmutable('+1 day');
        $periodicEntry = (new PeriodicEntry())
            ->setExecutionDate($tomorrow)
            ->setName('Test Entry')
            ->setAccount(new Account());

        $this->expectException(PeriodicEntrySplitBudgetException::class);
        $this->expectExceptionMessage('The periodic entry is not scheduled for today.');

        $this->messageBusMock
            ->expects(self::never())
            ->method('dispatch');

        $this->entityManagerMock
            ->expects(self::never())
            ->method('flush');

        $this->generatePeriodicEntryOperator()
            ->addSplitForBudgets($periodicEntry);
    }

    public function testSplitAlreadyDoneMustDoNothing(): void
    {
        $today         = new DateTimeImmutable();
        $executionDate = new DateTimeImmutable('first day of this month +15 days 14:00:00');

        $periodicEntry = (new PeriodicEntry())
            ->setExecutionDate($today)
            ->setLastExecutionDate($executionDate)
            ->setName('Already executed entry')
            ->setAccount(new Account());

        $this->expectException(PeriodicEntrySplitBudgetException::class);
        $this->expectExceptionMessage('A periodic entry has already been executed.');

        $this->messageBusMock
            ->expects(self::never())
            ->method('dispatch');

        $this->entityManagerMock
            ->expects(self::never())
            ->method('flush');

        $this->generatePeriodicEntryOperator()
            ->addSplitForBudgets($periodicEntry);
    }

    public function testSplitExecutedLastMonthShouldAllow(): void
    {
        $today              = new DateTimeImmutable();
        $lastMonthExecution = new DateTimeImmutable('first day of last month +15 days');

        $periodicEntry = (new PeriodicEntry())
            ->setExecutionDate($today)
            ->setLastExecutionDate($lastMonthExecution)
            ->setAmount(200.0)
            ->setName('Last month execution')
            ->setAccount(new Account());

        $this->messageBusMock
            ->expects(self::once())
            ->method('dispatch');

        $this->entityManagerMock
            ->expects(self::once())
            ->method('flush');

        $this->generatePeriodicEntryOperator()
            ->addSplitForBudgets($periodicEntry);
    }

    public function testSplitForecastWithAllBudgetsDisabledCreatesNoEntries(): void
    {
        $today = new DateTimeImmutable();

        $disabledBudget1 = (new Budget())
            ->setName('Disabled Budget 1')
            ->setAmount(100.0)
            ->setEnabled(false);

        $disabledBudget2 = (new Budget())
            ->setName('Disabled Budget 2')
            ->setAmount(200.0)
            ->setEnabled(false);

        $periodicEntry = (new PeriodicEntry())
            ->setExecutionDate($today)
            ->setLastExecutionDate(null)
            ->setName('All budgets disabled')
            ->setAccount(new Account())
            ->addBudget($disabledBudget1)
            ->addBudget($disabledBudget2);

        $this->messageBusMock
            ->expects(self::never())
            ->method('dispatch');

        $this->entityManagerMock
            ->expects(self::once())
            ->method('flush');

        $this->generatePeriodicEntryOperator()
            ->addSplitForBudgets($periodicEntry);
    }

    public function testSplitForecastWithZeroAmountBudgetsCreatesNoEntries(): void
    {
        $today = new DateTimeImmutable();

        $zeroBudget = (new Budget())
            ->setName('Zero Budget')
            ->setAmount(0.0)
            ->setEnabled(true);

        $periodicEntry = (new PeriodicEntry())
            ->setExecutionDate($today)
            ->setLastExecutionDate(null)
            ->setName('Zero amount budgets')
            ->setAccount(new Account())
            ->addBudget($zeroBudget);

        $this->messageBusMock
            ->expects(self::never())
            ->method('dispatch');

        $this->entityManagerMock
            ->expects(self::once())
            ->method('flush');

        $this->generatePeriodicEntryOperator()
            ->addSplitForBudgets($periodicEntry);
    }

    public function testSplitWithCustomDateParameter(): void
    {
        $customDate           = new DateTimeImmutable('today 10:00:00');
        $executionDateSameDay = new DateTimeImmutable('today 14:00:00');

        $periodicEntry = (new PeriodicEntry())
            ->setExecutionDate($executionDateSameDay)
            ->setLastExecutionDate(null)
            ->setAmount(300.0)
            ->setName('Custom date test')
            ->setAccount(new Account());

        $this->messageBusMock
            ->expects(self::once())
            ->method('dispatch');

        $this->entityManagerMock
            ->expects(self::once())
            ->method('flush');

        $this->generatePeriodicEntryOperator()
            ->addSplitForBudgets($periodicEntry, $customDate);
    }

    public function testSplitNotYetDoneForSpentMustCreateOnlyOneEntry(): void
    {
        $today = new DateTimeImmutable();

        $periodicEntry = (new PeriodicEntry())
            ->setExecutionDate($today)
            ->setLastExecutionDate(null)
            ->setAmount(200.0)
            ->setName('Spent Entry')
            ->setAccount(new Account());

        $this->messageBusMock
            ->expects(self::once())
            ->method('dispatch');

        $this->entityManagerMock
            ->expects(self::once())
            ->method('flush');

        $this->generatePeriodicEntryOperator()
            ->addSplitForBudgets($periodicEntry);

        self::assertNotNull($periodicEntry->getLastExecutionDate());
    }

    public function testSplitNotYetDoneForForecastMustCreateAllEntries(): void
    {
        $today = new DateTimeImmutable();

        $periodicEntry = (new PeriodicEntry())
            ->setExecutionDate($today)
            ->setLastExecutionDate(null)
            ->setName('Forecast Entry')
            ->setAccount(new Account())
            ->addBudget((new Budget())
                ->setName('Budget 1')
                ->setAmount(200.0)
                ->setEnabled(true)
            )
            ->addBudget((new Budget())
                ->setName('Budget 2')
                ->setAmount(300.0)
                ->setEnabled(true)
            );

        $this->messageBusMock
            ->expects(self::exactly(2))
            ->method('dispatch');

        $this->entityManagerMock
            ->expects(self::once())
            ->method('flush');

        $this->generatePeriodicEntryOperator()
            ->addSplitForBudgets($periodicEntry);

        self::assertNotNull($periodicEntry->getLastExecutionDate());
    }

    public function testSplitForecastMustCreateAllEntriesExceptDisabledBudgets(): void
    {
        $today = new DateTimeImmutable();

        $periodicEntry = (new PeriodicEntry())
            ->setExecutionDate($today)
            ->setLastExecutionDate(null)
            ->setName('Mixed budgets')
            ->setAccount(new Account())
            ->addBudget((new Budget())
                ->setName('Enabled Budget 1')
                ->setAmount(200.0)
                ->setEnabled(true)
            )
            ->addBudget((new Budget())
                ->setName('Enabled Budget 2')
                ->setAmount(300.0)
                ->setEnabled(true)
            )
            ->addBudget((new Budget())
                ->setName('Disabled Budget')
                ->setAmount(150.0)
                ->setEnabled(false)
            );

        $this->messageBusMock
            ->expects(self::exactly(2))
            ->method('dispatch');

        $this->entityManagerMock
            ->expects(self::once())
            ->method('flush');

        $this->generatePeriodicEntryOperator()
            ->addSplitForBudgets($periodicEntry);

        self::assertNotNull($periodicEntry->getLastExecutionDate());
    }
}
