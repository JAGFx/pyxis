<?php

namespace App\Tests\Unit\Shared\Message\Command\GenerateHistoryBudgetsForYear;

use App\Domain\Budget\Entity\Budget;
use App\Domain\Budget\Entity\HistoryBudget;
use App\Domain\Budget\Message\Command\CreateHistoryBudget\CreateHistoryBudgetCommand;
use App\Domain\Budget\Message\Query\FindBudgetVO\FindBudgetVOQuery;
use App\Domain\Budget\Message\Query\FindHistoryBudgets\FindHistoryBudgetsQuery;
use App\Domain\Budget\ValueObject\BudgetValueObject;
use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Infrastructure\Doctrine\Service\EntityFinder;
use App\Shared\Message\Command\GenerateHistoryBudgetsForYear\GenerateHistoryBudgetsForYearCommand;
use App\Shared\Message\Command\GenerateHistoryBudgetsForYear\GenerateHistoryBudgetsForYearHandler;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class GenerateHistoryBudgetsForYearHandlerTest extends TestCase
{
    private readonly MessageBus $messageBusMock;

    private readonly EntityFinder $entityFinderMock;

    protected function setUp(): void
    {
        $this->messageBusMock   = $this->createMock(MessageBus::class);
        $this->entityFinderMock = $this->createMock(EntityFinder::class);
    }

    private function generateGenerateHistoryBudgetsForYearHandler(): GenerateHistoryBudgetsForYearHandler
    {
        return new GenerateHistoryBudgetsForYearHandler(
            $this->createMock(LoggerInterface::class),
            $this->messageBusMock,
            $this->entityFinderMock,
        );
    }

    public function testNotYetHistoryCreatedMustCreateOneSuccessfully(): void
    {
        $this->messageBusMock
            ->expects($this->exactly(1 + 2 + 2)) // 1 FindBudgetVOQuery + 2 FindHistoryBudgetsQuery + 2 CreateHistoryBudgetCommand
            ->method('dispatch')
            ->willReturnCallback(function ($query) {
                if ($query instanceof FindBudgetVOQuery) {
                    return [
                        new BudgetValueObject(1, '#1', 20, true, 20),
                        new BudgetValueObject(2, '#2', 20, true, 20),
                    ];
                } elseif ($query instanceof FindHistoryBudgetsQuery) {
                    return [];
                } elseif ($query instanceof CreateHistoryBudgetCommand) {
                    return null;
                }

                throw new InvalidArgumentException('Unexpected query type: ' . get_class($query));
            });

        $this->entityFinderMock
            ->expects($this->exactly(2))
            ->method('findByIntIdentifier')
            ->willReturn(new Budget()->setAmount(20));

        $this
            ->generateGenerateHistoryBudgetsForYearHandler()
            ->__invoke(new GenerateHistoryBudgetsForYearCommand(2023));
    }

    public function testAlreadyHistoryCreatedMustDoNothing(): void
    {
        $this->messageBusMock
            ->expects($this->exactly(3)) // 1 FindBudgetVOQuery + 2 FindHistoryBudgetsQuery
            ->method('dispatch')
            ->willReturnCallback(function ($query) {
                if ($query instanceof FindBudgetVOQuery) {
                    return [
                        new BudgetValueObject(1, '#1', 20, true, 20),
                        new BudgetValueObject(2, '#2', 20, true, 20),
                    ];
                } elseif ($query instanceof FindHistoryBudgetsQuery) {
                    return [
                        new HistoryBudget(),
                    ];
                }

                throw new InvalidArgumentException('Unexpected query type: ' . get_class($query));
            });

        $this->entityFinderMock
            ->expects($this->exactly(2))
            ->method('findByIntIdentifier')
            ->willReturn(new Budget()->setAmount(20));

        $this
            ->generateGenerateHistoryBudgetsForYearHandler()
            ->__invoke(new GenerateHistoryBudgetsForYearCommand(2023));
    }
}
