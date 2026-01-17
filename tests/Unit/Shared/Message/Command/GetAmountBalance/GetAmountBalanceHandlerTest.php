<?php

namespace App\Tests\Unit\Shared\Message\Command\GetAmountBalance;

use App\Domain\Assignment\Message\Query\GetAssignmentBalance\GetAssignmentBalanceQuery;
use App\Domain\Entry\Message\Query\GetEntryBalance\GetEntryBalanceQuery;
use App\Domain\Entry\ValueObject\EntryBalance;
use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Infrastructure\Doctrine\Service\EntityFinder;
use App\Shared\Message\Query\GetAmountBalance\GetAmountBalanceHandler;
use App\Shared\Message\Query\GetAmountBalance\GetAmountBalanceQuery;
use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class GetAmountBalanceHandlerTest extends TestCase
{
    private MessageBus $messageBusMock;

    private EntityFinder $entityFinderMock;

    protected function setUp(): void
    {
        $this->messageBusMock   = $this->createMock(MessageBus::class);
        $this->entityFinderMock = $this->createMock(EntityFinder::class);
    }

    private function generateGetAmountBalanceHandler(): GetAmountBalanceHandler
    {
        return new GetAmountBalanceHandler(
            $this->messageBusMock,
            $this->entityFinderMock,
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

        $amountBalanceHandler = $this->generateGetAmountBalanceHandler();

        $amountBalances = $amountBalanceHandler->__invoke(new GetAmountBalanceQuery());
        self::assertCount(1, $amountBalances);

        $amountBalance = reset($amountBalances);

        self::assertSame($expectedTotal, $amountBalance->getTotal());
        self::assertSame($expectedSpent, $amountBalance->getTotalSpent());
        self::assertSame($expectedForecast, $amountBalance->getTotalForecast());
    }

    public function testAmountBalanceWithSpecificAccountIds(): void
    {
        $accountIds              = [1, 2, 3];
        $expectedCallsPerAccount = 2; // 1 GetEntryBalanceQuery + 1 GetAssignmentBalanceQuery per account
        $totalExpectedCalls      = count($accountIds) * $expectedCallsPerAccount;

        $this->messageBusMock
            ->expects($this->exactly($totalExpectedCalls))
            ->method('dispatch')
            ->willReturnCallback(function ($query) {
                if ($query instanceof GetEntryBalanceQuery) {
                    return new EntryBalance(100.0, 50.0);
                } elseif ($query instanceof GetAssignmentBalanceQuery) {
                    return 25.0;
                }

                throw new InvalidArgumentException('Unexpected query type: ' . get_class($query));
            });

        $amountBalanceHandler = $this->generateGetAmountBalanceHandler();

        $amountBalances = $amountBalanceHandler->__invoke(new GetAmountBalanceQuery($accountIds));

        // Verify we have one balance per account
        self::assertCount(count($accountIds), $amountBalances);

        // Verify each balance has the correct calculations
        foreach ($amountBalances as $amountBalance) {
            self::assertSame(150.0, $amountBalance->getTotal()); // 100 + 50
            self::assertSame(75.0, $amountBalance->getTotalSpent()); // 100 - 25 (assignment)
            self::assertSame(50.0, $amountBalance->getTotalForecast());
        }
    }
}
