<?php

namespace App\Tests\Integration\Shared\Operator;

use App\Domain\Account\Entity\Account;
use App\Shared\Operator\EntryOperator;
use App\Shared\ValueObject\AmountBalance;
use App\Tests\Factory\AccountFactory;
use App\Tests\Factory\AssignmentFactory;
use App\Tests\Factory\BudgetFactory;
use App\Tests\Factory\EntryFactory;
use App\Tests\Integration\Shared\KernelTestCase;

class EntryOperatorTest extends KernelTestCase
{
    private const string ACCOUNT_1 = 'Test Account 1';
    private const string ACCOUNT_2 = 'Test Account 2';
    private EntryOperator $entryOperator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->entryOperator = self::getContainer()->get(EntryOperator::class);
    }

    public function testGetAmountBalanceWithNoAccounts(): void
    {
        $result = $this->entryOperator->getAmountBalance();

        self::assertInstanceOf(AmountBalance::class, $result);
        self::assertEquals(0.0, $result->getTotalSpent());
        self::assertEquals(0.0, $result->getTotalForecast());
        self::assertEquals(0.0, $result->getAssignments());
        self::assertEquals(0.0, $result->getTotal());
    }

    public function testGetAmountBalanceWithAccountButNoEntriesNorAssignments(): void
    {
        AccountFactory::new()->create(['name' => self::ACCOUNT_1]);

        $result = $this->entryOperator->getAmountBalance();

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

        $result = $this->entryOperator->getAmountBalance();

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

        $result = $this->entryOperator->getAmountBalance($account1);

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

        $result = $this->entryOperator->getAmountBalance();

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

        $result = $this->entryOperator->getAmountBalance();

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
}
