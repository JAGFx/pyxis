<?php

namespace App\Tests\Integration\Shared\Message\Command\GenerateHistoryBudgetsForYear;

use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Shared\Message\Command\GenerateHistoryBudgetsForYear\GenerateHistoryBudgetsForYearCommand;
use App\Tests\Factory\AccountFactory;
use App\Tests\Factory\BudgetFactory;
use App\Tests\Factory\EntryFactory;
use App\Tests\Factory\HistoryBudgetFactory;
use App\Tests\Integration\Shared\KernelTestCase;
use DateTimeImmutable;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;

class GenerateHistoryBudgetsForYearHandlerTest extends KernelTestCase
{
    private MessageBus $messageBus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->messageBus = self::getContainer()->get(MessageBus::class);
    }

    public function testBudgetWithoutEntriesDoesNotGenerateHistory(): void
    {
        $testYear = 2025;

        // Create a budget without entries
        BudgetFactory::new()->create([
            'name'    => 'Budget without entries',
            'amount'  => 500.0,
            'enabled' => true,
        ]);

        // Create a budget with entries to ensure the method works
        $budgetWithEntries = BudgetFactory::new()->create([
            'name'    => 'Budget with entries',
            'amount'  => 300.0,
            'enabled' => true,
        ]);

        $account = AccountFactory::new()->create(['name' => 'Test Account']);
        EntryFactory::new()->create([
            'account'   => $account,
            'budget'    => $budgetWithEntries,
            'amount'    => -150.0,
            'name'      => 'Test expense',
            'createdAt' => new DateTimeImmutable("$testYear-01-01"),
        ]);

        $this->messageBus->dispatch(new GenerateHistoryBudgetsForYearCommand($testYear));

        $historyBudgets = HistoryBudgetFactory::repository()->findAll();

        // Only the budget with entries should have a history
        self::assertCount(1, $historyBudgets, 'Only the budget with entries should generate a history');

        $history = $historyBudgets[0];
        self::assertEquals($budgetWithEntries->_real(), $history->getBudget());
        self::assertEquals('Budget with entries', $history->getBudget()->getName());
    }

    public static function budgetProgressScenariosProvider(): Generator
    {
        yield 'Budget 0% spent (no entries)' => [
            'budgetAmount'         => 300.0,
            'entries'              => [],
            'expectedHistoryCount' => 0,
            'description'          => 'Budget without entries does not generate history',
        ];

        yield 'Budget 40% spent' => [
            'budgetAmount' => 500.0,
            'entries'      => [
                ['amount' => -200.0, 'name' => 'Gas'],
            ],
            'expectedHistoryCount' => 1,
            'description'          => 'Budget with moderate expenses',
        ];

        yield 'Budget 100% spent' => [
            'budgetAmount' => 400.0,
            'entries'      => [
                ['amount' => -400.0, 'name' => 'Complete budget purchase'],
            ],
            'expectedHistoryCount' => 1,
            'description'          => 'Budget fully spent',
        ];

        yield 'Budget overspent (+125%)' => [
            'budgetAmount' => 200.0,
            'entries'      => [
                ['amount' => -150.0, 'name' => 'First expense'],
                ['amount' => -100.0, 'name' => 'Second expense (overspent)'],
            ],
            'expectedHistoryCount' => 1,
            'description'          => 'Budget overspent',
        ];
    }

    #[DataProvider('budgetProgressScenariosProvider')]
    public function testBudgetHistoryGenerationWithDifferentSpendingScenarios(
        float $budgetAmount,
        array $entries,
        int $expectedHistoryCount,
        string $description,
    ): void {
        $testYear = 2025;
        $budget   = BudgetFactory::new()->create([
            'name'    => 'Test Budget - ' . $description,
            'amount'  => $budgetAmount,
            'enabled' => true,
        ]);

        if (!empty($entries)) {
            $account = AccountFactory::new()->create(['name' => 'Test Account']);
            foreach ($entries as $entryData) {
                EntryFactory::new()->create([
                    'account'   => $account,
                    'budget'    => $budget,
                    'amount'    => $entryData['amount'],
                    'name'      => $entryData['name'],
                    'createdAt' => new DateTimeImmutable("$testYear-01-01"),
                ]);
            }
        }

        $this->messageBus->dispatch(new GenerateHistoryBudgetsForYearCommand($testYear));

        $historyBudgets = HistoryBudgetFactory::repository()->findAll();
        self::assertCount($expectedHistoryCount, $historyBudgets, $description);

        if ($expectedHistoryCount > 0) {
            $history = $historyBudgets[0];
            self::assertEquals($budget->_real(), $history->getBudget());
            self::assertEquals($budgetAmount, $history->getAmount());
        }
    }

    public function testGenerateHistoryBudgetsForYearIdempotent(): void
    {
        $testYear = 2024;
        $account  = AccountFactory::new()->create(['name' => 'Idempotent Test Account']);
        $budget   = BudgetFactory::new()->create([
            'name'    => 'Test Idempotent Budget',
            'amount'  => 500.0,
            'enabled' => true,
        ]);

        EntryFactory::new()->create([
            'account'   => $account,
            'budget'    => $budget,
            'amount'    => -250.0,
            'name'      => 'Test expense',
            'createdAt' => new DateTimeImmutable("$testYear-01-01"),
        ]);

        $this->messageBus->dispatch(new GenerateHistoryBudgetsForYearCommand($testYear));
        $this->messageBus->dispatch(new GenerateHistoryBudgetsForYearCommand($testYear));

        $historyBudgets = HistoryBudgetFactory::repository()->findAll();

        self::assertCount(1, $historyBudgets, 'Should not create duplicate history budgets');
        self::assertEquals(250.0, $historyBudgets[0]->getSpent());
        self::assertEquals(50.0, $historyBudgets[0]->getRelativeProgress());
    }

    public static function largeBudgetDatasetProvider(): array
    {
        $datasets = [];

        for ($i = 1; $i <= 10; ++$i) {
            $budgetAmount = $i * 100.0;
            $entriesCount = rand(1, 3);
            $entries      = [];

            for ($j = 0; $j < $entriesCount; ++$j) {
                $entries[] = [
                    'amount' => -($budgetAmount / $entriesCount * 0.8), // Spend 80% distributed
                    'name'   => "Expense $j for Budget Category $i",
                ];
            }

            $datasets["Budget Category $i"] = [
                'categoryName' => "Budget Category $i",
                'budgetAmount' => $budgetAmount,
                'entries'      => $entries,
            ];
        }

        return $datasets;
    }

    #[DataProvider('largeBudgetDatasetProvider')]
    public function testGenerateHistoryBudgetsForYearWithLargeDataset(
        string $categoryName,
        float $budgetAmount,
        array $entries,
    ): void {
        $testYear = 2024;
        $account  = AccountFactory::new()->create(['name' => 'Large Dataset Account']);
        $budget   = BudgetFactory::new()->create([
            'name'    => $categoryName,
            'amount'  => $budgetAmount,
            'enabled' => true,
        ]);

        foreach ($entries as $entryData) {
            EntryFactory::new()->create([
                'account'   => $account,
                'budget'    => $budget,
                'amount'    => $entryData['amount'],
                'name'      => $entryData['name'],
                'createdAt' => new DateTimeImmutable("$testYear-01-01"),
            ]);
        }

        $this->messageBus->dispatch(new GenerateHistoryBudgetsForYearCommand($testYear));

        $historyBudgets = HistoryBudgetFactory::repository()->findAll();
        self::assertCount(1, $historyBudgets, "Should create history budget for $categoryName");

        $history = $historyBudgets[0];
        self::assertEquals($budgetAmount, $history->getAmount());
        self::assertGreaterThanOrEqual(0, $history->getSpent());
        self::assertGreaterThanOrEqual(0, $history->getRelativeProgress());
    }
}
