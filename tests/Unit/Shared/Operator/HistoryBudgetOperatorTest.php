<?php

namespace App\Tests\Unit\Shared\Operator;

use App\Domain\Budget\Entity\Budget;
use App\Domain\Budget\Entity\HistoryBudget;
use App\Domain\Budget\Manager\BudgetManager;
use App\Domain\Budget\Manager\HistoryBudgetManager;
use App\Domain\Budget\ValueObject\BudgetValueObject;
use App\Shared\Cqs\Bus\MessageBus;
use App\Shared\Operator\HistoryBudgetOperator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class HistoryBudgetOperatorTest extends TestCase
{
    private readonly BudgetManager $budgetManagerMock;
    private readonly HistoryBudgetManager $historyBudgetManagerMock;
    private readonly MessageBus $messageBusMock;

    protected function setUp(): void
    {
        $this->budgetManagerMock        = $this->createMock(BudgetManager::class);
        $this->historyBudgetManagerMock = $this->createMock(HistoryBudgetManager::class);
        $this->messageBusMock           = $this->createMock(MessageBus::class);
    }

    private function generateHistoryBudgetOperator(): HistoryBudgetOperator
    {
        return new HistoryBudgetOperator(
            $this->budgetManagerMock,
            $this->historyBudgetManagerMock,
            $this->createMock(LoggerInterface::class),
            $this->messageBusMock
        );
    }

    public function testNotYetHistoryCreatedMustCreateOneSuccessfully(): void
    {
        $this->messageBusMock
            ->expects($this->once())
            ->method('dispatch')
            ->willReturn([
                new BudgetValueObject(1, '#1', 20, true, 20),
                new BudgetValueObject(2, '#2', 20, true, 20),
            ]);

        $this->budgetManagerMock
            ->expects($this->exactly(2))
            ->method('find')
            ->willReturn((new Budget())->setAmount(20));

        $this->historyBudgetManagerMock
            ->expects($this->exactly(2))
            ->method('getHistories')
            ->willReturn([]);

        $this->historyBudgetManagerMock
            ->expects($this->exactly(2))
            ->method('create');

        $this
            ->generateHistoryBudgetOperator()
            ->generateHistoryBudgetsForYear(2023);
    }

    public function testAlreadyHistoryCreatedMustDoNothing(): void
    {
        $this->messageBusMock
            ->expects($this->once())
            ->method('dispatch')
            ->willReturn([
                new BudgetValueObject(1, '#1', 20, true, 20),
                new BudgetValueObject(2, '#2', 20, true, 20),
            ]);

        $this->budgetManagerMock
            ->expects($this->exactly(2))
            ->method('find')
            ->willReturn((new Budget())->setAmount(20));

        $this->historyBudgetManagerMock
            ->expects($this->exactly(2))
            ->method('getHistories')
            ->willReturn([new HistoryBudget()]);

        $this->historyBudgetManagerMock
            ->expects($this->never())
            ->method('create');

        $this
            ->generateHistoryBudgetOperator()
            ->generateHistoryBudgetsForYear(2023);
    }
}
