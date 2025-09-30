<?php

namespace App\Tests\Integration\Shared\Message\Query\GetBudgetBalanceProgresses;

use App\Domain\Budget\ValueObject\BudgetBalanceProgressValueObject;
use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Shared\Message\Query\GetBudgetBalanceProgresses\GetBudgetBalanceProgressesQuery;
use App\Shared\Utils\YearRange;
use App\Tests\Factory\AccountFactory;
use App\Tests\Factory\BudgetFactory;
use App\Tests\Factory\EntryFactory;
use App\Tests\Factory\HistoryBudgetFactory;
use App\Tests\Integration\Shared\KernelTestCase;
use DateTimeImmutable;

class GetBudgetBalanceProgressesHandlerTest extends KernelTestCase
{
    private const string ACCOUNT_1 = 'Test Account';
    private MessageBus $messageBus;

    protected function setUp(): void
    {
        self::bootKernel();
        $container        = static::getContainer();
        $this->messageBus = $container->get(MessageBus::class);
    }

    public function testGetBudgetBalanceProgressesWithNoBudgetExpensesNorHistory(): void
    {
        BudgetFactory::new()->create([
            'name'    => 'Budget Without Expenses',
            'amount'  => 500.0,
            'enabled' => true,
        ]);

        $searchQuery = new GetBudgetBalanceProgressesQuery(YearRange::current(), false);

        /** @var BudgetBalanceProgressValueObject[] $result */
        $result = $this->messageBus->dispatch($searchQuery);

        self::assertIsArray($result);
        self::assertCount(0, $result);
    }

    public function testGetBudgetBalanceProgressesCurrentYearOnlyWithoutHistory(): void
    {
        $account = AccountFactory::new()->create(['name' => self::ACCOUNT_1]);

        $budget = BudgetFactory::new()->create([
            'name'    => 'Current Year Budget',
            'amount'  => 800.0,
            'enabled' => true,
        ]);
        $testYear = YearRange::current();

        // Create current year entries only (negative amounts = expenses)
        EntryFactory::new()->create([
            'account'   => $account,
            'budget'    => $budget,
            'amount'    => -200.0, // Negative = expense
            'name'      => 'Current expense 1',
            'createdAt' => new DateTimeImmutable($testYear),
        ]);

        EntryFactory::new()->create([
            'account'   => $account,
            'budget'    => $budget,
            'amount'    => -150.0, // Negative = expense
            'name'      => 'Current expense 2',
            'createdAt' => new DateTimeImmutable($testYear),
        ]);

        // Test current year
        $searchQuery = new GetBudgetBalanceProgressesQuery(YearRange::current(), false);

        /** @var BudgetBalanceProgressValueObject[] $result */
        $result = $this->messageBus->dispatch($searchQuery);

        self::assertIsArray($result);
        self::assertCount(1, $result);

        $progressVO = $result[0];
        self::assertEquals('Current Year Budget', $progressVO->getName());
        self::assertEquals(800.0, $progressVO->getAmount());
        self::assertEquals(350.0, $progressVO->getProgress()); // abs(-200) + abs(-150) = 350
        self::assertEquals(43.75, $progressVO->getTrueRelativeProgress()); // (350/800) * 100
    }

    public function testGetBudgetBalanceProgressesWithCurrentAndHistoricalData(): void
    {
        $account = AccountFactory::new()->create(['name' => self::ACCOUNT_1]);

        $budget = BudgetFactory::new()->create([
            'name'    => 'Complete Budget',
            'amount'  => 1200.0,
            'enabled' => true,
        ]);

        $testYear = YearRange::current();

        // Create current year entries (negative amounts = expenses)
        EntryFactory::new()->create([
            'account'   => $account,
            'budget'    => $budget,
            'amount'    => -300.0, // Negative = expense
            'name'      => '2025 expense',
            'createdAt' => new DateTimeImmutable($testYear),
        ]);

        EntryFactory::new()->create([
            'account'   => $account,
            'budget'    => $budget,
            'amount'    => -250.0, // Negative = expense
            'name'      => 'Another 2025 expense',
            'createdAt' => new DateTimeImmutable($testYear),
        ]);

        // Create historical data for previous years
        HistoryBudgetFactory::new()->create([
            'budget'           => $budget,
            'date'             => new DateTimeImmutable('2024-12-31'),
            'amount'           => 1000.0,
            'spent'            => 800.0,
            'relativeProgress' => 80.0,
        ]);

        HistoryBudgetFactory::new()->create([
            'budget'           => $budget,
            'date'             => new DateTimeImmutable('2023-12-31'),
            'amount'           => 900.0,
            'spent'            => 750.0,
            'relativeProgress' => 83.33,
        ]);

        // Test current year (should use entries, not history)
        $currentYearSearchQuery = new GetBudgetBalanceProgressesQuery(YearRange::current(), false);

        /** @var BudgetBalanceProgressValueObject[] $currentResult */
        $currentResult = $this->messageBus->dispatch($currentYearSearchQuery);

        self::assertCount(1, $currentResult);
        $currentProgressVO = $currentResult[0];
        self::assertEquals('Complete Budget', $currentProgressVO->getName());
        self::assertEquals(1200.0, $currentProgressVO->getAmount());
        self::assertEquals(550.0, $currentProgressVO->getProgress()); // abs(-300) + abs(-250) = 550 (from entries)

        // Test historical year 2024 (should use history)
        $historicalSearchQuery2024 = new GetBudgetBalanceProgressesQuery(2024, false);

        /** @var BudgetBalanceProgressValueObject[] $historical2024Result */
        $historical2024Result = $this->messageBus->dispatch($historicalSearchQuery2024);

        self::assertCount(1, $historical2024Result);
        $historical2024ProgressVO = $historical2024Result[0];
        self::assertEquals('Complete Budget', $historical2024ProgressVO->getName());
        self::assertEquals(1000.0, $historical2024ProgressVO->getAmount());
        self::assertEquals(800.0, $historical2024ProgressVO->getProgress()); // from history
        self::assertEquals(80.0, $historical2024ProgressVO->getTrueRelativeProgress());

        // Test historical year 2023 (should use history)
        $historicalSearchQuery2023 = new GetBudgetBalanceProgressesQuery(2023, false);

        /** @var BudgetBalanceProgressValueObject[] $historical2023Result */
        $historical2023Result = $this->messageBus->dispatch($historicalSearchQuery2023);

        self::assertCount(1, $historical2023Result);
        $historical2023ProgressVO = $historical2023Result[0];
        self::assertEquals('Complete Budget', $historical2023ProgressVO->getName());
        self::assertEquals(900.0, $historical2023ProgressVO->getAmount());
        self::assertEquals(750.0, $historical2023ProgressVO->getProgress()); // from history
        self::assertEquals(83.33, $historical2023ProgressVO->getTrueRelativeProgress());
    }

    public function testGetBudgetBalanceProgressesNoCurrentExpensesButHistoricalData(): void
    {
        $budget = BudgetFactory::new()->create([
            'name'    => 'Historical Only Budget',
            'amount'  => 600.0,
            'enabled' => true,
        ]);

        // Create historical data only
        HistoryBudgetFactory::new()->create([
            'budget'           => $budget,
            'date'             => new DateTimeImmutable('2024-12-31'),
            'amount'           => 600.0,
            'spent'            => 450.0,
            'relativeProgress' => 75.0,
        ]);

        HistoryBudgetFactory::new()->create([
            'budget'           => $budget,
            'date'             => new DateTimeImmutable('2023-12-31'),
            'amount'           => 550.0,
            'spent'            => 200.0,
            'relativeProgress' => 36.36,
        ]);

        // Test current year (should show budget with 0 progress since no entries)
        $currentYearSearchQuery = new GetBudgetBalanceProgressesQuery(YearRange::current(), false);

        /** @var BudgetBalanceProgressValueObject[] $currentResult */
        $currentResult = $this->messageBus->dispatch($currentYearSearchQuery);

        self::assertEmpty($currentResult);

        // Test historical year 2024 (should use history)
        $historicalSearchQuery2024 = new GetBudgetBalanceProgressesQuery(2024, false);

        /** @var BudgetBalanceProgressValueObject[] $historical2024Result */
        $historical2024Result = $this->messageBus->dispatch($historicalSearchQuery2024);

        self::assertCount(1, $historical2024Result);
        $historical2024ProgressVO = $historical2024Result[0];
        self::assertEquals('Historical Only Budget', $historical2024ProgressVO->getName());
        self::assertEquals(600.0, $historical2024ProgressVO->getAmount());
        self::assertEquals(450.0, $historical2024ProgressVO->getProgress()); // from history
        self::assertEquals(75.0, $historical2024ProgressVO->getTrueRelativeProgress());

        // Test historical year 2023 (should use history)
        $historicalSearchQuery2023 = new GetBudgetBalanceProgressesQuery(2023, false);

        /** @var BudgetBalanceProgressValueObject[] $historical2023Result */
        $historical2023Result = $this->messageBus->dispatch($historicalSearchQuery2023);

        self::assertCount(1, $historical2023Result);
        $historical2023ProgressVO = $historical2023Result[0];
        self::assertEquals('Historical Only Budget', $historical2023ProgressVO->getName());
        self::assertEquals(550.0, $historical2023ProgressVO->getAmount());
        self::assertEquals(200.0, $historical2023ProgressVO->getProgress()); // from history
        self::assertEquals(36.36, $historical2023ProgressVO->getTrueRelativeProgress());
    }

    public function testGetBudgetBalanceProgressesMultipleBudgetsWithMixedScenarios(): void
    {
        $account = AccountFactory::new()->create(['name' => self::ACCOUNT_1]);

        // Budget 1: Current expenses only (no history)
        $budget1 = BudgetFactory::new()->create([
            'name'    => 'Current Only Budget',
            'amount'  => 400.0,
            'enabled' => true,
        ]);

        $testYear = YearRange::current();

        EntryFactory::new()->create([
            'account'   => $account,
            'budget'    => $budget1,
            'amount'    => -200.0, // Negative = expense
            'name'      => 'Budget 1 expense',
            'createdAt' => new DateTimeImmutable($testYear),
        ]);

        // Budget 2: No current expenses, only history
        $budget2 = BudgetFactory::new()->create([
            'name'    => 'Historical Only Budget 2',
            'amount'  => 300.0,
            'enabled' => true,
        ]);

        HistoryBudgetFactory::new()->create([
            'budget'           => $budget2,
            'date'             => new DateTimeImmutable('2024-12-31'),
            'amount'           => 300.0,
            'spent'            => 250.0,
            'relativeProgress' => 83.33,
        ]);

        // Budget 3: Both current expenses AND history
        $budget3 = BudgetFactory::new()->create([
            'name'    => 'Mixed Budget',
            'amount'  => 500.0,
            'enabled' => true,
        ]);

        EntryFactory::new()->create([
            'account'   => $account,
            'budget'    => $budget3,
            'amount'    => -150.0, // Negative = expense
            'name'      => 'Budget 3 expense',
            'createdAt' => new DateTimeImmutable($testYear),
        ]);

        HistoryBudgetFactory::new()->create([
            'budget'           => $budget3,
            'date'             => new DateTimeImmutable('2024-12-31'),
            'amount'           => 500.0,
            'spent'            => 400.0,
            'relativeProgress' => 80.0,
        ]);

        // Test current year
        $currentYearSearchQuery = new GetBudgetBalanceProgressesQuery(YearRange::current(), false);

        /** @var BudgetBalanceProgressValueObject[] $currentResult */
        $currentResult = $this->messageBus->dispatch($currentYearSearchQuery);

        self::assertCount(2, $currentResult);

        // Sort by name for predictable assertions
        usort($currentResult, fn ($a, $b) => strcmp($a->getName(), $b->getName()));

        // Current Only Budget
        self::assertEquals('Current Only Budget', $currentResult[0]->getName());
        self::assertEquals(200.0, $currentResult[0]->getProgress()); // abs(-200)

        // Mixed Budget (current expenses, not history)
        self::assertEquals('Mixed Budget', $currentResult[1]->getName());
        self::assertEquals(150.0, $currentResult[1]->getProgress()); // abs(-150)

        // Test historical year 2024
        $historicalSearchQuery = new GetBudgetBalanceProgressesQuery(2024, false);

        /** @var BudgetBalanceProgressValueObject[] $historicalResult */
        $historicalResult = $this->messageBus->dispatch($historicalSearchQuery);

        self::assertCount(2, $historicalResult); // Only budgets with history for 2024

        // Sort by name for predictable assertions
        usort($historicalResult, fn ($a, $b) => strcmp($a->getName(), $b->getName()));

        // Historical Only Budget 2
        self::assertEquals('Historical Only Budget 2', $historicalResult[0]->getName());
        self::assertEquals(250.0, $historicalResult[0]->getProgress());

        // Mixed Budget
        self::assertEquals('Mixed Budget', $historicalResult[1]->getName());
        self::assertEquals(400.0, $historicalResult[1]->getProgress());
    }
}
