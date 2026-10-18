<?php

namespace App\Tests\Unit\Shared\Message\Command\AddSplitForBudgets;

use App\Domain\Account\Entity\Account;
use App\Domain\Assignment\Entity\Assignment;
use App\Domain\Budget\Entity\Budget;
use App\Domain\Entry\Message\Command\CreateOrUpdateEntry\CreateOrUpdateEntryCommand;
use App\Domain\PeriodicEntry\Entity\PeriodicEntry;
use App\Domain\PeriodicEntry\Exception\PeriodicEntrySplitBudgetException;
use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Infrastructure\Doctrine\Service\EntityFinder;
use App\Shared\Message\Command\AddSplitForBudgets\AddSplitForBudgetsCommand;
use App\Shared\Message\Command\AddSplitForBudgets\AddSplitForBudgetsHandler;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class AddSplitForBudgetsHandlerTest extends TestCase
{
    private EntityManagerInterface $entityManagerMock;

    private MessageBus $messageBusMock;

    private EntityFinder $entityFinderMock;

    protected function setUp(): void
    {
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->messageBusMock    = $this->createMock(MessageBus::class);
        $this->entityFinderMock  = $this->createMock(EntityFinder::class);
    }

    private function generateAddSplitForBudgetsHandler(): AddSplitForBudgetsHandler
    {
        return new AddSplitForBudgetsHandler(
            $this->entityManagerMock,
            $this->messageBusMock,
            $this->entityFinderMock,
            $this->createStub(LoggerInterface::class),
        );
    }

    public function testAddSplitForBudgetsThrowsExceptionWhenNotScheduledForToday(): void
    {
        $tomorrow      = new DateTimeImmutable('+1 day');
        $periodicEntry = new PeriodicEntry()
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

        $this->entityFinderMock
            ->expects(self::once())
            ->method('findByIntIdentifierOrFail')
            ->willReturn($periodicEntry);

        $this->generateAddSplitForBudgetsHandler()
            ->__invoke(new AddSplitForBudgetsCommand($periodicEntry->getId()));
    }

    public function testSplitAlreadyDoneMustDoNothing(): void
    {
        $today         = new DateTimeImmutable();
        $executionDate = new DateTimeImmutable('first day of this month +15 days 14:00:00');

        $periodicEntry = new PeriodicEntry()
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

        $this->entityFinderMock
            ->expects(self::once())
            ->method('findByIntIdentifierOrFail')
            ->willReturn($periodicEntry);

        $this->generateAddSplitForBudgetsHandler()
            ->__invoke(new AddSplitForBudgetsCommand($periodicEntry->getId()));
    }

    public function testSplitExecutedLastMonthShouldAllow(): void
    {
        $today              = new DateTimeImmutable();
        $lastMonthExecution = new DateTimeImmutable('first day of last month +15 days');

        $periodicEntry = new PeriodicEntry()
            ->setExecutionDate($today)
            ->setLastExecutionDate($lastMonthExecution)
            ->setAmount(20000)
            ->setName('Last month execution')
            ->setAccount(new Account());

        $this->messageBusMock
            ->expects(self::once())
            ->method('dispatch');

        $this->entityManagerMock
            ->expects(self::once())
            ->method('flush');

        $this->entityFinderMock
            ->expects(self::once())
            ->method('findByIntIdentifierOrFail')
            ->willReturn($periodicEntry);

        $this->generateAddSplitForBudgetsHandler()
            ->__invoke(new AddSplitForBudgetsCommand($periodicEntry->getId()));
    }

    public function testSplitForecastWithAllBudgetsDisabledCreatesNoEntries(): void
    {
        $today = new DateTimeImmutable();

        $disabledBudget1 = new Budget()
            ->setName('Disabled Budget 1')
            ->setAmount(10000)
            ->setEnabled(false);

        $disabledBudget2 = new Budget()
            ->setName('Disabled Budget 2')
            ->setAmount(20000)
            ->setEnabled(false);

        $periodicEntry = new PeriodicEntry()
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

        $this->entityFinderMock
            ->expects(self::once())
            ->method('findByIntIdentifierOrFail')
            ->willReturn($periodicEntry);

        $this->generateAddSplitForBudgetsHandler()
            ->__invoke(new AddSplitForBudgetsCommand($periodicEntry->getId()));
    }

    public function testSplitForecastWithZeroAmountBudgetsCreatesNoEntries(): void
    {
        $today = new DateTimeImmutable();

        $zeroBudget = (new Budget())
            ->setName('Zero Budget')
            ->setAmount(0)
            ->setEnabled(true);

        $periodicEntry = new PeriodicEntry()
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

        $this->entityFinderMock
            ->expects(self::once())
            ->method('findByIntIdentifierOrFail')
            ->willReturn($periodicEntry);

        $this->generateAddSplitForBudgetsHandler()
            ->__invoke(new AddSplitForBudgetsCommand($periodicEntry->getId()));
    }

    public function testSplitWithCustomDateParameter(): void
    {
        $customDate           = new DateTimeImmutable('today 10:00:00');
        $executionDateSameDay = new DateTimeImmutable('today 14:00:00');

        $periodicEntry = new PeriodicEntry()
            ->setExecutionDate($executionDateSameDay)
            ->setLastExecutionDate(null)
            ->setAmount(30000)
            ->setName('Custom date test')
            ->setAccount(new Account());

        $this->messageBusMock
            ->expects(self::once())
            ->method('dispatch');

        $this->entityManagerMock
            ->expects(self::once())
            ->method('flush');

        $this->entityFinderMock
            ->expects(self::once())
            ->method('findByIntIdentifierOrFail')
            ->willReturn($periodicEntry);

        $this->generateAddSplitForBudgetsHandler()
            ->__invoke(new AddSplitForBudgetsCommand($periodicEntry->getId(), $customDate));
    }

    public function testSplitNotYetDoneForSpentMustCreateOnlyOneEntry(): void
    {
        $today = new DateTimeImmutable();

        $periodicEntry = new PeriodicEntry()
            ->setExecutionDate($today)
            ->setLastExecutionDate(null)
            ->setAmount(20000)
            ->setName('Spent Entry')
            ->setAccount(new Account());

        $this->messageBusMock
            ->expects(self::once())
            ->method('dispatch');

        $this->entityManagerMock
            ->expects(self::once())
            ->method('flush');

        $this->entityFinderMock
            ->expects(self::once())
            ->method('findByIntIdentifierOrFail')
            ->willReturn($periodicEntry);

        $this->generateAddSplitForBudgetsHandler()
            ->__invoke(new AddSplitForBudgetsCommand($periodicEntry->getId()));

        self::assertNotNull($periodicEntry->getLastExecutionDate());
    }

    public function testSplitNotYetDoneForForecastMustCreateAllEntries(): void
    {
        $today = new DateTimeImmutable();

        $periodicEntry = new PeriodicEntry()
            ->setExecutionDate($today)
            ->setLastExecutionDate(null)
            ->setName('Forecast Entry')
            ->setAccount(new Account())
            ->addBudget(new Budget()
                ->setName('Budget 1')
                ->setAmount(20000)
                ->setEnabled(true)
            )
            ->addBudget(new Budget()
                ->setName('Budget 2')
                ->setAmount(30000)
                ->setEnabled(true)
            );

        $this->messageBusMock
            ->expects(self::exactly(2))
            ->method('dispatch');

        $this->entityManagerMock
            ->expects(self::once())
            ->method('flush');

        $this->entityFinderMock
            ->expects(self::once())
            ->method('findByIntIdentifierOrFail')
            ->willReturn($periodicEntry);

        $this->generateAddSplitForBudgetsHandler()
            ->__invoke(new AddSplitForBudgetsCommand($periodicEntry->getId()));

        self::assertNotNull($periodicEntry->getLastExecutionDate());
    }

    public function testSplitForecastMustCreateAllEntriesExceptDisabledBudgets(): void
    {
        $today = new DateTimeImmutable();

        $periodicEntry = new PeriodicEntry()
            ->setExecutionDate($today)
            ->setLastExecutionDate(null)
            ->setName('Mixed budgets')
            ->setAccount(new Account())
            ->addBudget(new Budget()
                ->setName('Enabled Budget 1')
                ->setAmount(20000)
                ->setEnabled(true)
            )
            ->addBudget(new Budget()
                ->setName('Enabled Budget 2')
                ->setAmount(30000)
                ->setEnabled(true)
            )
            ->addBudget(new Budget()
                ->setName('Disabled Budget')
                ->setAmount(15000)
                ->setEnabled(false)
            );

        $this->messageBusMock
            ->expects(self::exactly(2))
            ->method('dispatch');

        $this->entityManagerMock
            ->expects(self::once())
            ->method('flush');

        $this->entityFinderMock
            ->expects(self::once())
            ->method('findByIntIdentifierOrFail')
            ->willReturn($periodicEntry);

        $this->generateAddSplitForBudgetsHandler()
            ->__invoke(new AddSplitForBudgetsCommand($periodicEntry->getId()));

        self::assertNotNull($periodicEntry->getLastExecutionDate());
    }

    public function testSplitForSpentWithAssignmentMustPassAssignmentToEntry(): void
    {
        $assignment = new Assignment()
            ->setName('Savings')
            ->setAmount(100000);

        $periodicEntry = new PeriodicEntry()
            ->setExecutionDate(new DateTimeImmutable())
            ->setLastExecutionDate(null)
            ->setAmount(20000)
            ->setName('Spent Entry With Assignment')
            ->setAccount(new Account())
            ->setAssignment($assignment);

        $this->messageBusMock
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(
                static fn (CreateOrUpdateEntryCommand $command): bool => $command->getAssignment() === $assignment
                    && 20000 === $command->getAmount()
            ));

        $this->entityManagerMock
            ->expects(self::once())
            ->method('flush');

        $this->entityFinderMock
            ->expects(self::once())
            ->method('findByIntIdentifierOrFail')
            ->willReturn($periodicEntry);

        $this->generateAddSplitForBudgetsHandler()
            ->__invoke(new AddSplitForBudgetsCommand($periodicEntry->getId()));
    }

    public function testSplitForForecastMustNeverPassAssignmentToEntries(): void
    {
        $periodicEntry = new PeriodicEntry()
            ->setExecutionDate(new DateTimeImmutable())
            ->setLastExecutionDate(null)
            ->setName('Forecast Entry With Assignment')
            ->setAccount(new Account())
            ->setAssignment(new Assignment()->setName('Savings')->setAmount(100000))
            ->addBudget(new Budget()
                ->setName('Budget 1')
                ->setAmount(20000)
                ->setEnabled(true)
            )
            ->addBudget(new Budget()
                ->setName('Budget 2')
                ->setAmount(30000)
                ->setEnabled(true)
            );

        $this->messageBusMock
            ->expects(self::exactly(2))
            ->method('dispatch')
            ->with(self::callback(
                static fn (CreateOrUpdateEntryCommand $command): bool => null === $command->getAssignment()
            ));

        $this->entityManagerMock
            ->expects(self::once())
            ->method('flush');

        $this->entityFinderMock
            ->expects(self::once())
            ->method('findByIntIdentifierOrFail')
            ->willReturn($periodicEntry);

        $this->generateAddSplitForBudgetsHandler()
            ->__invoke(new AddSplitForBudgetsCommand($periodicEntry->getId()));
    }
}
