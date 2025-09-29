<?php

namespace App\Tests\Integration\Shared\Message\Command\Transfer;

use App\Domain\Account\Entity\Account;
use App\Domain\Budget\Entity\Budget;
use App\Domain\Entry\Entity\EntryFlagEnum;
use App\Domain\Entry\Message\Query\GetEntryBalance\GetEntryBalanceQuery;
use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Shared\Message\Command\Transfer\TransferCommand;
use App\Tests\Factory\AccountFactory;
use App\Tests\Factory\BudgetFactory;
use App\Tests\Factory\EntryFactory;
use App\Tests\Integration\Shared\KernelTestCase;

class TransferHandlerTest extends KernelTestCase
{
    private const string BUDGET_SOURCE_NAME = 'Budget source name';
    private const string BUDGET_TARGET_NAME = 'Budget target name';
    private const float BUDGET_AMOUNT       = 100.0;
    private MessageBus $messageBus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->messageBus = self::getContainer()->get(MessageBus::class);
    }

    public function testTransferMustMoveAmountFromSpentToBudgetSuccessfully(): void
    {
        /** @var Budget $budgetTarget */
        $budgetTarget = BudgetFactory::findOrCreate([
            'name'   => self::BUDGET_TARGET_NAME,
            'amount' => 0,
        ])->_real();

        $initialBalance = $this->messageBus->dispatch(new GetEntryBalanceQuery());
        $this->transfer(null, $budgetTarget);

        $newBalance = $this->messageBus->dispatch(new GetEntryBalanceQuery());
        self::assertSame($initialBalance->getTotalSpent() - self::BUDGET_AMOUNT, $newBalance->getTotalSpent());
        self::assertSame($initialBalance->getTotalForecast() + self::BUDGET_AMOUNT, $newBalance->getTotalForecast());
        self::assertSame(
            $initialBalance->getTotalSpent() + $initialBalance->getTotalForecast(), $newBalance->getTotalSpent() + $newBalance->getTotalForecast());
    }

    public function testTransferMustMoveAmountBetweenTwoBudgetSuccessfully(): void
    {
        /** @var Budget $budgetSource */
        $budgetSource = BudgetFactory::findOrCreate([
            'name'   => self::BUDGET_SOURCE_NAME,
            'amount' => 0,
        ])->_real();

        /** @var Budget $budgetTarget */
        $budgetTarget = BudgetFactory::findOrCreate([
            'name'   => self::BUDGET_TARGET_NAME,
            'amount' => 0,
        ])->_real();

        $initialBalance = $this->messageBus->dispatch(new GetEntryBalanceQuery());
        $this->transfer($budgetSource, $budgetTarget);

        $newBalance = $this->messageBus->dispatch(new GetEntryBalanceQuery());
        self::assertSame(
            $initialBalance->getTotalSpent() + $initialBalance->getTotalForecast(), $newBalance->getTotalSpent() + $newBalance->getTotalForecast());
    }

    public function testTransferMustMoveAmountBudgetToSpentSuccessfully(): void
    {
        /** @var Budget $budgetSource */
        $budgetSource = BudgetFactory::findOrCreate([
            'name'   => self::BUDGET_SOURCE_NAME,
            'amount' => 0,
        ])->_real();

        $initialBalance = $this->messageBus->dispatch(new GetEntryBalanceQuery());
        $this->transfer($budgetSource, null);

        $newBalance = $this->messageBus->dispatch(new GetEntryBalanceQuery());
        self::assertSame($initialBalance->getTotalSpent() + self::BUDGET_AMOUNT, $newBalance->getTotalSpent());
        self::assertSame($initialBalance->getTotalForecast() - self::BUDGET_AMOUNT, $newBalance->getTotalForecast());
        self::assertSame(
            $initialBalance->getTotalSpent() + $initialBalance->getTotalForecast(), $newBalance->getTotalSpent() + $newBalance->getTotalForecast());
    }

    private function transfer(?Budget $budgetSource, ?Budget $budgetTarget): void
    {
        /** @var Account $account */
        $account = AccountFactory::new()->create()->_real();

        $transfer = new TransferCommand()
            ->setAccount($account)
            ->setAmount(self::BUDGET_AMOUNT)
            ->setBudgetSource($budgetSource)
            ->setBudgetTarget($budgetTarget);

        $this->messageBus->dispatch($transfer);

        self::assertSame(-self::BUDGET_AMOUNT, $budgetSource?->getProgress() ?? -self::BUDGET_AMOUNT);
        self::assertSame(self::BUDGET_AMOUNT, $budgetTarget?->getProgress() ?? self::BUDGET_AMOUNT);

        // Add assert to get entries created of transfert and be sure count of them
        $lastTwoById = EntryFactory::repository()->findBy([], ['id' => 'ASC'], 2);
        self::assertCount(2, $lastTwoById);

        foreach ($lastTwoById as $item) {
            self::assertSame([EntryFlagEnum::TRANSFERT], $item->getFlags());
        }

        if (null !== $budgetSource) {
            $entrySource = $lastTwoById[0];
            self::assertSame($account, $entrySource->getAccount());
            self::assertSame(-self::BUDGET_AMOUNT, $entrySource->getAmount());
            self::assertSame($budgetSource, $entrySource->getBudget());
            self::assertSame(self::BUDGET_SOURCE_NAME, $entrySource->getName());
        }

        if (null !== $budgetTarget) {
            $entryTarget = $lastTwoById[1];
            self::assertSame($account, $entryTarget->getAccount());
            self::assertSame(self::BUDGET_AMOUNT, $entryTarget->getAmount());
            self::assertSame($budgetTarget, $entryTarget->getBudget());
            self::assertSame(self::BUDGET_TARGET_NAME, $entryTarget->getName());
        }
    }
}
