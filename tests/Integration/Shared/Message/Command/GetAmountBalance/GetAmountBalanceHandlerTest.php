<?php

namespace App\Tests\Integration\Shared\Message\Command\GetAmountBalance;

use App\Domain\Account\Entity\Account;
use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Shared\Message\Query\GetAmountBalance\GetAmountBalanceQuery;
use App\Shared\ValueObject\AmountBalance;
use App\Tests\Factory\AccountFactory;
use App\Tests\Factory\AssignmentFactory;
use App\Tests\Factory\BudgetFactory;
use App\Tests\Factory\EntryFactory;
use App\Tests\Integration\Shared\KernelTestCase;

class GetAmountBalanceHandlerTest extends KernelTestCase
{
    private const string ACCOUNT_1 = 'Test Account 1';
    private const string ACCOUNT_2 = 'Test Account 2';
    private MessageBus $messageBus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->messageBus = self::getContainer()->get(MessageBus::class);
    }

    public function testGetAmountBalanceWithNoAccounts(): void
    {
        $result = $this->messageBus->dispatch(new GetAmountBalanceQuery());

        self::assertCount(1, $result);
        $result = reset($result);

        self::assertInstanceOf(AmountBalance::class, $result);
        self::assertEquals(0.0, $result->getTotalSpent());
        self::assertEquals(0.0, $result->getTotalForecast());
        self::assertEquals(0.0, $result->getAssignments());
        self::assertEquals(0.0, $result->getTotal());
    }

    public function testGetAmountBalanceWithAccountButNoEntriesNorAssignments(): void
    {
        AccountFactory::new()->create(['name' => self::ACCOUNT_1]);

        $result = $this->messageBus->dispatch(new GetAmountBalanceQuery());

        self::assertCount(1, $result);
        $result = reset($result);

        self::assertInstanceOf(AmountBalance::class, $result);
        self::assertEquals(0.0, $result->getTotalSpent());
        self::assertEquals(0.0, $result->getTotalForecast());
        self::assertEquals(0.0, $result->getAssignments());
        self::assertEquals(0.0, $result->getTotal());
    }

    public function testGetAmountBalanceWithPositiveSpentEntries(): void
    {
        $account = AccountFactory::new()->create(['name' => self::ACCOUNT_1]);
        $budget  = BudgetFactory::new()->create(['name' => 'Monthly Budget', 'amount' => 1000.0]);

        // Create positive spent entries (money allocated to spend, not actual expenses)
        EntryFactory::new()->create([
            'account' => $account,
            'budget'  => null, // Spent entry
            'amount'  => 600.0, // Positive = money allocated to spend
            'name'    => 'Monthly spending allocation',
        ]);

        // Create forecast entries (provisions)
        EntryFactory::new()->create([
            'account' => $account,
            'budget'  => $budget, // Forecast entry
            'amount'  => 300.0,
            'name'    => 'Budget provision',
        ]);

        // Create assignments (subtracted from remaining to spend)
        AssignmentFactory::new()->create([
            'account' => $account,
            'amount'  => 150.0,
            'name'    => 'Rent assignment',
        ]);

        $result = $this->messageBus->dispatch(new GetAmountBalanceQuery());

        self::assertCount(1, $result);
        $result = reset($result);

        self::assertInstanceOf(AmountBalance::class, $result);

        // Total spent = remaining to spend = 600 - 150 = 450
        self::assertEquals(450.0, $result->getTotalSpent());

        // Total forecast = 300 (provisions)
        self::assertEquals(300.0, $result->getTotalForecast());

        // Assignments = 150
        self::assertEquals(150.0, $result->getAssignments());

        // Total = 450 + 300 + 150 = 900
        self::assertEquals(900.0, $result->getTotal());
    }

    public function testGetAmountBalanceForSpecificAccount(): void
    {
        /** @var Account $account1 */
        $account1 = AccountFactory::new()->create(['name' => self::ACCOUNT_1])->_real();
        $account2 = AccountFactory::new()->create(['name' => self::ACCOUNT_2]);
        $budget   = BudgetFactory::new()->create(['name' => 'Test Budget', 'amount' => 800.0]);

        // Create entries for account1
        EntryFactory::new()->create([
            'account' => $account1,
            'budget'  => null, // Spent entry (positive = allocated to spend)
            'amount'  => 400.0,
            'name'    => 'Account 1 spending allocation',
        ]);

        EntryFactory::new()->create([
            'account' => $account1,
            'budget'  => $budget, // Forecast entry (provision)
            'amount'  => 300.0,
            'name'    => 'Account 1 provision',
        ]);

        // Create entries for account2 (should be ignored when filtering by account1)
        EntryFactory::new()->create([
            'account' => $account2,
            'budget'  => null,
            'amount'  => 200.0,
            'name'    => 'Account 2 spending allocation',
        ]);

        EntryFactory::new()->create([
            'account' => $account2,
            'budget'  => $budget,
            'amount'  => 150.0,
            'name'    => 'Account 2 provision',
        ]);

        // Create assignments
        AssignmentFactory::new()->create([
            'account' => $account1,
            'amount'  => 100.0,
            'name'    => 'Account 1 assignment',
        ]);

        AssignmentFactory::new()->create([
            'account' => $account2,
            'amount'  => 50.0,
            'name'    => 'Account 2 assignment',
        ]);

        $result = $this->messageBus->dispatch(new GetAmountBalanceQuery([$account1->getId()]));

        self::assertCount(1, $result);
        $result = reset($result);

        self::assertInstanceOf(AmountBalance::class, $result);

        // Total spent for account1 = remaining to spend = 400 - 100 = 300
        self::assertEquals(300.0, $result->getTotalSpent());

        // Total forecast for account1 = 300 (provisions)
        self::assertEquals(300.0, $result->getTotalForecast());

        // Assignments for account1 = 100
        self::assertEquals(100.0, $result->getAssignments());

        // Total = 300 + 300 + 100 = 700
        self::assertEquals(700.0, $result->getTotal());
    }

    public function testGetAmountBalanceWithMultipleAccounts(): void
    {
        $account1 = AccountFactory::new()->create(['name' => self::ACCOUNT_1]);
        $account2 = AccountFactory::new()->create(['name' => self::ACCOUNT_2]);
        AccountFactory::new()->create(['name' => 'Empty Account']);

        $budget1 = BudgetFactory::new()->create(['name' => 'Budget 1', 'amount' => 600.0]);
        $budget2 = BudgetFactory::new()->create(['name' => 'Budget 2', 'amount' => 400.0]);

        // Account 1: Has both spent allocation and provisions with assignments
        EntryFactory::new()->create([
            'account' => $account1,
            'budget'  => null, // Spent entry (money allocated to spend)
            'amount'  => 500.0,
            'name'    => 'Account 1 spending allocation',
        ]);

        EntryFactory::new()->create([
            'account' => $account1,
            'budget'  => $budget1, // Provision
            'amount'  => 350.0,
            'name'    => 'Account 1 provision',
        ]);

        AssignmentFactory::new()->create([
            'account' => $account1,
            'amount'  => 120.0,
            'name'    => 'Account 1 assignment',
        ]);

        // Account 2: Has only provisions, no assignments
        EntryFactory::new()->create([
            'account' => $account2,
            'budget'  => $budget2, // Provision only
            'amount'  => 200.0,
            'name'    => 'Account 2 provision only',
        ]);

        $result = $this->messageBus->dispatch(new GetAmountBalanceQuery());

        self::assertCount(1, $result);
        $result = reset($result);

        self::assertInstanceOf(AmountBalance::class, $result);

        // Total spent = remaining to spend = 500 - 120 = 380
        self::assertEquals(380.0, $result->getTotalSpent());

        // Total forecast = 350 + 200 = 550 (provisions)
        self::assertEquals(550.0, $result->getTotalForecast());

        // Total assignments = 120
        self::assertEquals(120.0, $result->getAssignments());

        // Total = 380 + 550 + 120 = 1050
        self::assertEquals(1050.0, $result->getTotal());
    }

    public function testGetAmountBalanceWithOnlyForecastEntries(): void
    {
        $account = AccountFactory::new()->create(['name' => self::ACCOUNT_1]);
        $budget  = BudgetFactory::new()->create(['name' => 'Forecast Budget', 'amount' => 500.0]);

        EntryFactory::new()->create([
            'account' => $account,
            'budget'  => $budget,
            'amount'  => 350.0,
            'name'    => 'Only forecast entry',
        ]);

        $result = $this->messageBus->dispatch(new GetAmountBalanceQuery());

        self::assertCount(1, $result);
        $result = reset($result);

        self::assertInstanceOf(AmountBalance::class, $result);

        // No spent entries: entryBalance.getTotalSpent() - assignmentsBalance = 0 - 0 = 0
        self::assertEquals(0.0, $result->getTotalSpent());

        // Total forecast = 350
        self::assertEquals(350.0, $result->getTotalForecast());

        // No assignments
        self::assertEquals(0.0, $result->getAssignments());

        // Total = 0 + 350 + 0 = 350
        self::assertEquals(350.0, $result->getTotal());
    }

    public function testGetAmountBalanceWithMultipleSpecificAccounts(): void
    {
        /** @var Account $account1 */
        $account1 = AccountFactory::new()->create(['name' => self::ACCOUNT_1])->_real();
        /** @var Account $account2 */
        $account2 = AccountFactory::new()->create(['name' => self::ACCOUNT_2])->_real();
        // Create a third account that won't be queried
        AccountFactory::new()->create(['name' => 'Ignored Account']);

        $budget1 = BudgetFactory::new()->create(['name' => 'Budget 1', 'amount' => 800.0]);
        $budget2 = BudgetFactory::new()->create(['name' => 'Budget 2', 'amount' => 600.0]);

        // Account 1 setup: spent + forecast + assignment
        EntryFactory::new()->create([
            'account' => $account1,
            'budget'  => null, // Spent entry
            'amount'  => 1000.0,
            'name'    => 'Account 1 spending allocation',
        ]);

        EntryFactory::new()->create([
            'account' => $account1,
            'budget'  => $budget1, // Forecast entry
            'amount'  => 400.0,
            'name'    => 'Account 1 provision',
        ]);

        AssignmentFactory::new()->create([
            'account' => $account1,
            'amount'  => 300.0,
            'name'    => 'Account 1 assignment',
        ]);

        // Account 2 setup: spent + forecast + assignment (different values)
        EntryFactory::new()->create([
            'account' => $account2,
            'budget'  => null, // Spent entry
            'amount'  => 600.0,
            'name'    => 'Account 2 spending allocation',
        ]);

        EntryFactory::new()->create([
            'account' => $account2,
            'budget'  => $budget2, // Forecast entry
            'amount'  => 250.0,
            'name'    => 'Account 2 provision',
        ]);

        AssignmentFactory::new()->create([
            'account' => $account2,
            'amount'  => 100.0,
            'name'    => 'Account 2 assignment',
        ]);

        // Create noise in the ignored account
        EntryFactory::new()->create([
            'account' => AccountFactory::new()->create(['name' => 'Ignored Account']),
            'budget'  => null,
            'amount'  => 9999.0,
            'name'    => 'Should be ignored',
        ]);

        // Query specific accounts
        /** @var AmountBalance[] $result */
        $result = $this->messageBus->dispatch(new GetAmountBalanceQuery([$account1->getId(), $account2->getId()]));

        // Should have exactly 2 results (one per account)
        self::assertCount(2, $result);

        // Extract balances by account name for easier testing
        $balancesByName = [];
        foreach ($result as $balance) {
            $balancesByName[$balance->getAccountName()] = $balance;
        }

        // Verify Account 1 balance
        self::assertArrayHasKey(self::ACCOUNT_1, $balancesByName);
        $account1Balance = $balancesByName[self::ACCOUNT_1];

        // Account 1: spent = 1000 - 300 = 700, forecast = 400, assignments = 300, total = 700 + 400 + 300 = 1400
        self::assertEquals(700.0, $account1Balance->getTotalSpent());
        self::assertEquals(400.0, $account1Balance->getTotalForecast());
        self::assertEquals(300.0, $account1Balance->getAssignments());
        self::assertEquals(1400.0, $account1Balance->getTotal());

        // Verify Account 2 balance
        self::assertArrayHasKey(self::ACCOUNT_2, $balancesByName);
        $account2Balance = $balancesByName[self::ACCOUNT_2];

        // Account 2: spent = 600 - 100 = 500, forecast = 250, assignments = 100, total = 500 + 250 + 100 = 850
        self::assertEquals(500.0, $account2Balance->getTotalSpent());
        self::assertEquals(250.0, $account2Balance->getTotalForecast());
        self::assertEquals(100.0, $account2Balance->getAssignments());
        self::assertEquals(850.0, $account2Balance->getTotal());
    }
}
