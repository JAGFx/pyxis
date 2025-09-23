<?php

namespace App\Tests\Unit\Shared\Operator;

use App\Domain\Entry\Manager\EntryManager;
use App\Domain\Entry\ValueObject\EntryBalance;
use App\Shared\Cqs\Bus\MessageBus;
use App\Shared\Operator\EntryOperator;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class EntryOperatorTest extends TestCase
{
    private EntryManager $entryManagerMock;
    private MessageBus $messageBusMock;

    protected function setUp(): void
    {
        $this->entryManagerMock = $this->createMock(EntryManager::class);
        $this->messageBusMock   = $this->createMock(MessageBus::class);
    }

    private function generateEntryOperator(): EntryOperator
    {
        return new EntryOperator(
            $this->entryManagerMock,
            $this->messageBusMock
        );
    }

    public static function amountBalanceDatasets(): Generator
    {
        yield 'Without data' => [
            'totalSpent'       => null,
            'totalForecast'    => null,
            'totalAssignments' => null,
            'expectedTotal'    => 0.0,
            'expectedSpent'    => 0.0,
            'expectedForecast' => 0.0,
        ];

        yield 'With spent / No forecast / No assignment' => [
            'totalSpent'       => 1000,
            'totalForecast'    => null,
            'totalAssignments' => null,
            'expectedTotal'    => 1000,
            'expectedSpent'    => 1000,
            'expectedForecast' => 0.0,
        ];

        yield 'With spent / With forecast / No assignment' => [
            'totalSpent'       => 1000,
            'totalForecast'    => 500,
            'totalAssignments' => null,
            'expectedTotal'    => 1500,
            'expectedSpent'    => 1000,
            'expectedForecast' => 500,
        ];

        yield 'With spent / With forecast / With assignment' => [
            'totalSpent'       => 1000,
            'totalForecast'    => 500,
            'totalAssignments' => 200,
            'expectedTotal'    => 1500,
            'expectedSpent'    => 800, // rawSpent - assignment
            'expectedForecast' => 500,
        ];
    }

    #[DataProvider('amountBalanceDatasets')]
    public function testAmountBalance(
        ?float $totalSpent,
        ?float $totalForecast,
        ?float $totalAssignments,
        float $expectedTotal,
        float $expectedSpent,
        float $expectedForecast,
    ): void {
        $this->entryManagerMock
            ->expects($this->once())
            ->method('balance')
            ->willReturn(new EntryBalance(
                $totalSpent ?? 0.0,
                $totalForecast ?? 0.0,
            ));

        $this->messageBusMock
            ->expects($this->once())
            ->method('dispatch')
            ->willReturn($totalAssignments ?? 0.0);

        $entryOperator = $this->generateEntryOperator();

        $amountBalance = $entryOperator->getAmountBalance();
        self::assertSame($expectedTotal, $amountBalance->getTotal());
        self::assertSame($expectedSpent, $amountBalance->getTotalSpent());
        self::assertSame($expectedForecast, $amountBalance->getTotalForecast());
    }
}
