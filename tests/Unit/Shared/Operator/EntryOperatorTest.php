<?php

namespace App\Tests\Unit\Shared\Operator;

use App\Domain\Assignment\Message\Query\GetAssignmentBalance\GetAssignmentBalanceQuery;
use App\Domain\Entry\Message\Query\GetEntryBalance\GetEntryBalanceQuery;
use App\Domain\Entry\ValueObject\EntryBalance;
use App\Infrastructure\Cqs\Bus\SymfonyMessageBus;
use App\Shared\Operator\EntryOperator;
use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class EntryOperatorTest extends TestCase
{
    private SymfonyMessageBus $messageBusMock;

    protected function setUp(): void
    {
        $this->messageBusMock = $this->createMock(SymfonyMessageBus::class);
    }

    private function generateEntryOperator(): EntryOperator
    {
        return new EntryOperator(
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
        $this->messageBusMock
            ->expects($this->exactly(2)) // 1 GetEntryBalanceQuery + 1 GetAssignmentBalanceQuery
            ->method('dispatch')
            ->willReturnCallback(function ($query) use ($totalSpent, $totalForecast, $totalAssignments) {
                if ($query instanceof GetEntryBalanceQuery) {
                    return new EntryBalance(
                        $totalSpent ?? 0.0,
                        $totalForecast ?? 0.0,
                    );
                } elseif ($query instanceof GetAssignmentBalanceQuery) {
                    return $totalAssignments ?? 0.0;
                }

                throw new InvalidArgumentException('Unexpected query type: ' . get_class($query));
            });

        $entryOperator = $this->generateEntryOperator();

        $amountBalance = $entryOperator->getAmountBalance();
        self::assertSame($expectedTotal, $amountBalance->getTotal());
        self::assertSame($expectedSpent, $amountBalance->getTotalSpent());
        self::assertSame($expectedForecast, $amountBalance->getTotalForecast());
    }
}
