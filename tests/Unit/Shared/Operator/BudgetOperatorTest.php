<?php

namespace App\Tests\Unit\Shared\Operator;

use App\Domain\Account\Entity\Account;
use App\Domain\Account\Manager\AccountManager;
use App\Domain\Budget\Entity\Budget;
use App\Domain\Budget\Manager\BudgetManager;
use App\Domain\Budget\Manager\HistoryBudgetManager;
use App\Domain\Budget\ValueObject\BudgetCashFlowByAccountValueObject;
use App\Shared\Operator\BudgetOperator;
use PHPUnit\Framework\TestCase;

class BudgetOperatorTest extends TestCase
{
    private BudgetManager $budgetManager;
    private HistoryBudgetManager $historyBudgetManager;
    private AccountManager $accountManager;

    protected function setUp(): void
    {
        $this->budgetManager        = $this->createMock(BudgetManager::class);
        $this->historyBudgetManager = $this->createMock(HistoryBudgetManager::class);
        $this->accountManager       = $this->createMock(AccountManager::class);
    }

    private function createBudgetOperator(): BudgetOperator
    {
        return new BudgetOperator(
            $this->budgetManager,
            $this->historyBudgetManager,
            $this->accountManager
        );
    }

    private function createAccount(string $name): Account
    {
        $account = new Account();
        $account->setName($name);
        $account->setEnable(true);

        return $account;
    }

    private function createBudget(string $name, float $amount = 1000.0): Budget
    {
        $budget = new Budget();
        $budget->setName($name);
        $budget->setAmount($amount);

        return $budget;
    }

    private function setupAccountManagerMock(array $accounts): void
    {
        $this->accountManager
            ->expects(self::once())
            ->method('getAccounts')
            ->willReturn($accounts);
    }

    public function testGetBudgetCashFlowsByAccountWithPositiveCashFlows(): void
    {
        $account1 = $this->createAccount('Account 1');
        $account2 = $this->createAccount('Account 2');
        $account3 = $this->createAccount('Account 3');

        $accounts = [$account1, $account2, $account3];

        $this->setupAccountManagerMock($accounts);

        $budgetMock = $this->createMock(Budget::class);
        $budgetMock->expects(self::exactly(3))
            ->method('getCashFlow')
            ->willReturnCallback(function ($account) use ($account1, $account2) {
                return match ($account) {
                    $account1 => 150.0,  // Positive cash flow
                    $account2 => -75.0,  // Negative cash flow
                    default   => 0.0,
                };
            });

        $result = $this->createBudgetOperator()
            ->getBudgetCashFlowsByAccount($budgetMock);

        self::assertIsArray($result);
        self::assertCount(2, $result); // Only accounts with non-zero cash flows

        foreach ($result as $cashFlowVO) {
            self::assertInstanceOf(BudgetCashFlowByAccountValueObject::class, $cashFlowVO);
        }

        // Check first cash flow (Account 1)
        self::assertEquals($budgetMock, $result[0]->getBudget());
        self::assertEquals($account1, $result[0]->getAccount());
        self::assertEquals(150.0, $result[0]->getCashFlow());

        // Check second cash flow (Account 2)
        self::assertEquals($budgetMock, $result[1]->getBudget());
        self::assertEquals($account2, $result[1]->getAccount());
        self::assertEquals(-75.0, $result[1]->getCashFlow());
    }

    public function testGetBudgetCashFlowsByAccountWithAllZeroCashFlows(): void
    {
        $account1 = $this->createAccount('Account 1');
        $account2 = $this->createAccount('Account 2');

        $accounts = [$account1, $account2];

        $this->setupAccountManagerMock($accounts);

        $budgetMock = $this->createMock(Budget::class);
        $budgetMock->expects(self::exactly(2))
            ->method('getCashFlow')
            ->willReturn(0.0);

        $result = $this->createBudgetOperator()
            ->getBudgetCashFlowsByAccount($budgetMock);

        self::assertIsArray($result);
        self::assertEmpty($result); // No accounts should be returned
    }

    public function testGetBudgetCashFlowsByAccountWithNoAccounts(): void
    {
        $accounts = []; // No accounts

        $this->setupAccountManagerMock($accounts);

        $budgetMock = $this->createMock(Budget::class);
        $budgetMock->expects(self::never())
            ->method('getCashFlow'); // Should not be called since no accounts

        $result = $this->createBudgetOperator()
            ->getBudgetCashFlowsByAccount($budgetMock);

        self::assertIsArray($result);
        self::assertEmpty($result);
    }

    public function testGetBudgetCashFlowsByAccountWithOnlyNegativeCashFlows(): void
    {
        $account1 = $this->createAccount('Deficit Account 1');
        $account2 = $this->createAccount('Deficit Account 2');

        $accounts = [$account1, $account2];

        $this->setupAccountManagerMock($accounts);

        $budgetMock = $this->createMock(Budget::class);
        $budgetMock->expects(self::exactly(2))
            ->method('getCashFlow')
            ->willReturnCallback(function ($account) use ($account1, $account2) {
                return match ($account) {
                    $account1 => -200.0,
                    $account2 => -350.0,
                    default   => 0.0,
                };
            });

        $result = $this->createBudgetOperator()
            ->getBudgetCashFlowsByAccount($budgetMock);

        self::assertIsArray($result);
        self::assertCount(2, $result);

        // Check all are negative cash flows
        self::assertEquals(-200.0, $result[0]->getCashFlow());
        self::assertEquals(-350.0, $result[1]->getCashFlow());
    }

    public function testGetBudgetCashFlowsByAccountWithMixedCashFlowsIncludingSmallValues(): void
    {
        $account1 = $this->createAccount('Small Positive');
        $account2 = $this->createAccount('Small Negative');
        $account3 = $this->createAccount('Exact Zero');
        $account4 = $this->createAccount('Large Positive');

        $accounts = [$account1, $account2, $account3, $account4];

        $this->setupAccountManagerMock($accounts);

        $budgetMock = $this->createMock(Budget::class);
        $budgetMock->expects(self::exactly(4))
            ->method('getCashFlow')
            ->willReturnCallback(function ($account) use ($account1, $account2, $account3, $account4) {
                return match ($account) {
                    $account1 => 0.01,    // Small positive
                    $account2 => -0.01,   // Small negative
                    $account3 => 0.0,     // Exact zero (excluded)
                    $account4 => 1000.0,  // Large positive
                    default   => 0.0,
                };
            });

        $result = $this->createBudgetOperator()
            ->getBudgetCashFlowsByAccount($budgetMock);

        self::assertIsArray($result);
        self::assertCount(3, $result); // All except the zero one

        $cashFlows = array_map(fn ($vo) => $vo->getCashFlow(), $result);
        self::assertContains(0.01, $cashFlows);
        self::assertContains(-0.01, $cashFlows);
        self::assertContains(1000.0, $cashFlows);
        self::assertNotContains(0.0, $cashFlows);
    }
}
