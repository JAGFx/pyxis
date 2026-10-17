<?php

namespace App\Tests\Unit\Shared\Message\Command\GetAmountBalance;

use App\Domain\Account\Entity\Account;
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
            'expectedTotal'    => 0,
            'expectedSpent'    => 0,
            'expectedForecast' => 0,
        ];

        yield 'With spent / No forecast / No assignment' => [
            'totalSpent'       => 100000,
            'totalForecast'    => null,
            'totalAssignments' => null,
            'expectedTotal'    => 100000,
            'expectedSpent'    => 100000,
            'expectedForecast' => 0,
        ];

        yield 'With spent / With forecast / No assignment' => [
            'totalSpent'       => 100000,
            'totalForecast'    => 50000,
            'totalAssignments' => null,
            'expectedTotal'    => 150000,
            'expectedSpent'    => 100000,
            'expectedForecast' => 50000,
        ];

        yield 'With spent / With forecast / With assignment' => [
            'totalSpent'       => 100000,
            'totalForecast'    => 50000,
            'totalAssignments' => 20000,
            'expectedTotal'    => 150000,
            'expectedSpent'    => 80000, // rawSpent - assignment
            'expectedForecast' => 50000,
        ];
    }

    #[DataProvider('amountBalanceDatasets')]
    public function testAmountBalance(
        ?int $totalSpent,
        ?int $totalForecast,
        ?int $totalAssignments,
        int $expectedTotal,
        int $expectedSpent,
        int $expectedForecast,
    ): void {
        $this->messageBusMock
            ->expects($this->exactly(2)) // 1 GetEntryBalanceQuery + 1 GetAssignmentBalanceQuery
            ->method('dispatch')
            ->willReturnCallback(function ($query) use ($totalSpent, $totalForecast, $totalAssignments) {
                if ($query instanceof GetEntryBalanceQuery) {
                    return new EntryBalance(
                        $totalSpent ?? 0,
                        $totalForecast ?? 0,
                    );
                } elseif ($query instanceof GetAssignmentBalanceQuery) {
                    return $totalAssignments ?? 0;
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
                    return new EntryBalance(10000, 5000);
                } elseif ($query instanceof GetAssignmentBalanceQuery) {
                    return 2500;
                }

                throw new InvalidArgumentException('Unexpected query type: ' . get_class($query));
            });

        $this->entityFinderMock
            ->expects($this->exactly(count($accountIds)))
            ->method('findByIntIdentifierOrFail')
            ->willReturnCallback(function (string $entityClass, int $id): Account {
                $account = $this->createMock(Account::class);
                $account->method('getId')->willReturn($id);

                return $account;
            });

        $amountBalanceHandler = $this->generateGetAmountBalanceHandler();

        $amountBalances = $amountBalanceHandler->__invoke(new GetAmountBalanceQuery($accountIds));

        // Verify we have one balance per account
        self::assertCount(count($accountIds), $amountBalances);

        // Verify each balance has the correct calculations
        foreach ($amountBalances as $amountBalance) {
            self::assertSame(15000, $amountBalance->getTotal()); // 100 + 50
            self::assertSame(7500, $amountBalance->getTotalSpent()); // 100 - 25 (assignment)
            self::assertSame(5000, $amountBalance->getTotalForecast());
        }
    }
}
