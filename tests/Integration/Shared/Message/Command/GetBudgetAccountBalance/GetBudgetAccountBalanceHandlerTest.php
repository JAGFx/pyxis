<?php

namespace App\Tests\Integration\Shared\Message\Command\GetBudgetAccountBalance;

use App\Domain\Account\Entity\Account;
use App\Domain\Budget\Entity\Budget;
use App\Domain\Entry\Entity\Entry;
use App\Domain\Entry\Entity\EntryFlagEnum;
use App\Domain\Entry\Message\Query\GetEntryBalance\GetEntryBalanceQuery;
use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Shared\Message\Command\ApplyBudgetAccountBalance\ApplyBudgetAccountBalanceCommand;
use App\Tests\Factory\AccountFactory;
use App\Tests\Factory\BudgetFactory;
use App\Tests\Factory\EntryFactory;
use App\Tests\Integration\Shared\KernelTestCase;
use DateTimeImmutable;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;

class GetBudgetAccountBalanceHandlerTest extends KernelTestCase
{
    private const string BUDGET_BALANCE_NAME = 'Budget balance';

    private MessageBus $messageBus;

    protected function setUp(): void
    {
        self::bootKernel();
        $container        = static::getContainer();
        $this->messageBus = $container->get(MessageBus::class);
    }

    private function populateBalanceDatabase(float $cashFlowAmount = 500.0): void
    {
        /** @var Budget $budget */
        $budget = BudgetFactory::createOne([
            'name'   => self::BUDGET_BALANCE_NAME,
            'amount' => 1000.0,
        ])->_real();

        /** @var Account $account */
        $account = AccountFactory::new()
            ->createOne()
            ->_real();

        EntryFactory::createSequence([
            [
                'createdAt' => new DateTimeImmutable('-5 hour'),
                'amount'    => $cashFlowAmount,
                'budget'    => $budget,
                'account'   => $account,
            ],
            [
                'createdAt' => new DateTimeImmutable('-1 year -1 hour'),
                'amount'    => 200,
                'budget'    => $budget,
                'account'   => $account,
            ],
        ]);
    }

    public function testBalancingWithoutPositiveOrNegativeMistDoNothing(): void
    {
        /** @var Budget $budget */
        $budget = BudgetFactory::createOne([
            'name'   => self::BUDGET_BALANCE_NAME,
            'amount' => 1000.0,
        ])->_real();

        /** @var Account $account */
        $account = AccountFactory::new()
            ->createOne()
            ->_real();

        $initialBalance = $this->messageBus->dispatch(new GetEntryBalanceQuery());

        $this->messageBus->dispatch(new ApplyBudgetAccountBalanceCommand(
            budget: $budget,
            account: $account,
        ));

        $newBalance = $this->messageBus->dispatch(new GetEntryBalanceQuery());

        self::assertSame($initialBalance->getTotalSpent(), $newBalance->getTotalSpent());
        self::assertSame($initialBalance->getTotalForecast(), $newBalance->getTotalForecast());
    }

    public static function budgetBalancingDataset(): Generator
    {
        yield 'Positive cash flow' => [500.0];
        yield 'Negative cash flow' => [-500.0];
    }

    #[DataProvider('budgetBalancingDataset')]
    public function testBudgetCashFlowMustTransferToSpent(float $cashFlowAmount): void
    {
        $this->populateBalanceDatabase($cashFlowAmount);

        $initialBalance = $this->messageBus->dispatch(new GetEntryBalanceQuery());
        $overflow       = 200.0;

        /** @var Budget $budget */
        $budget = BudgetFactory::find([
            'name' => self::BUDGET_BALANCE_NAME,
        ])->_real();

        /** @var Account $account */
        $account = AccountFactory::first()->_real();

        $this->messageBus->dispatch(new ApplyBudgetAccountBalanceCommand(
            budget: $budget,
            account: $account,
        ));

        $newBalance = $this->messageBus->dispatch(new GetEntryBalanceQuery());

        self::assertSame($initialBalance->getTotalSpent() + $overflow, $newBalance->getTotalSpent());
        self::assertSame($initialBalance->getTotalForecast() - $overflow, $newBalance->getTotalForecast());
        self::assertSame(0.0, $budget->getCashFlow());

        /** @var Entry[] $lastTwoById */
        $lastTwoById = EntryFactory::repository()->findBy([], ['id' => 'ASC'], 2, 2);

        self::assertCount(2, $lastTwoById);

        foreach ($lastTwoById as $item) {
            self::assertSame([EntryFlagEnum::BALANCE], $item->getFlags());
        }

        // Test entry spent
        self::assertSame($overflow, $lastTwoById[0]->getAmount());
        self::assertNull($lastTwoById[0]->getBudget());
        self::assertSame($account, $lastTwoById[0]->getAccount());

        // Test entry forecast
        self::assertSame(-$overflow, $lastTwoById[1]->getAmount());
        self::assertSame($budget, $lastTwoById[1]->getBudget());
        self::assertSame($account, $lastTwoById[1]->getAccount());
        self::assertSame($budget->getId(), $lastTwoById[1]->getBudget()?->getId());
    }

    public function testBalancingOnlyAppliesCashFlowOfTheSelectedAccount(): void
    {
        /** @var Budget $budget */
        $budget = BudgetFactory::createOne([
            'name'   => self::BUDGET_BALANCE_NAME,
            'amount' => 1000.0,
        ])->_real();

        /** @var Account $firstAccount */
        $firstAccount = AccountFactory::createOne()->_real();
        /** @var Account $secondAccount */
        $secondAccount = AccountFactory::createOne()->_real();

        EntryFactory::createSequence([
            [
                'createdAt' => new DateTimeImmutable('-1 year'),
                'amount'    => 300.0,
                'budget'    => $budget,
                'account'   => $firstAccount,
            ],
            [
                'createdAt' => new DateTimeImmutable('-1 year -1 hour'),
                'amount'    => -100.0,
                'budget'    => $budget,
                'account'   => $secondAccount,
            ],
        ]);

        $this->messageBus->dispatch(new ApplyBudgetAccountBalanceCommand(
            budget: $budget,
            account: $firstAccount,
        ));

        self::assertSame(0.0, $budget->getCashFlow($firstAccount));
        self::assertSame(-100.0, $budget->getCashFlow($secondAccount));
        self::assertTrue($budget->hasCashFlowToBalance());

        /** @var Entry[] $balanceEntries */
        $balanceEntries = EntryFactory::repository()->findBy([], ['id' => 'ASC'], 2, 2);

        self::assertCount(2, $balanceEntries);
        self::assertSame(300.0, $balanceEntries[0]->getAmount());
        self::assertSame($firstAccount, $balanceEntries[0]->getAccount());
        self::assertSame(-300.0, $balanceEntries[1]->getAmount());
        self::assertSame($firstAccount, $balanceEntries[1]->getAccount());

        $this->messageBus->dispatch(new ApplyBudgetAccountBalanceCommand(
            budget: $budget,
            account: $secondAccount,
        ));

        self::assertSame(0.0, $budget->getCashFlow($secondAccount));
        self::assertSame(0.0, $budget->getCashFlow());
        self::assertFalse($budget->hasCashFlowToBalance());
    }
}
