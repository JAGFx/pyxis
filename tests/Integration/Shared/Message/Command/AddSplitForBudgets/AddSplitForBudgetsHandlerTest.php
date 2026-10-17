<?php

namespace App\Tests\Integration\Shared\Message\Command\AddSplitForBudgets;

use App\Domain\Entry\Entity\Entry;
use App\Domain\Entry\Entity\EntryFlagEnum;
use App\Domain\PeriodicEntry\Entity\PeriodicEntry;
use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Shared\Message\Command\AddSplitForBudgets\AddSplitForBudgetsCommand;
use App\Tests\Factory\AccountFactory;
use App\Tests\Factory\BudgetFactory;
use App\Tests\Factory\EntryFactory;
use App\Tests\Factory\PeriodicEntryFactory;
use App\Tests\Integration\Shared\KernelTestCase;
use DateTimeImmutable;

class AddSplitForBudgetsHandlerTest extends KernelTestCase
{
    private MessageBus $messageBus;

    protected function setUp(): void
    {
        self::bootKernel();
        $container        = static::getContainer();
        $this->messageBus = $container->get(MessageBus::class);
    }

    public function testAddSplitForBudgetsSpentTypeCreatesOneEntry(): void
    {
        $account = AccountFactory::new()->create(['name' => 'Test Expense Account']);

        /** @var PeriodicEntry $periodicEntry */
        $periodicEntry = PeriodicEntryFactory::new()->create([
            'name'              => 'Monthly salary',
            'amount'            => 250000,
            'account'           => $account,
            'executionDate'     => new DateTimeImmutable(), // Today
            'lastExecutionDate' => null, // Never executed
            'budgets'           => [], // No budget = SPENT type
        ])->_real();

        $this->messageBus->dispatch(new AddSplitForBudgetsCommand($periodicEntry->getId()));

        /** @var Entry[] $createdEntries */
        $createdEntries = EntryFactory::repository()->findAll();

        self::assertCount(1, $createdEntries, 'Only one entry must be created for SPENT type');

        $entry = $createdEntries[0];
        self::assertEquals(250000, $entry->getAmount());
        self::assertEquals('Monthly salary', $entry->getName());
        self::assertEquals($account->_real(), $entry->getAccount());
        self::assertEquals([EntryFlagEnum::PERIODIC_ENTRY], $entry->getFlags());
        self::assertNull($entry->getBudget(), 'SPENT type entry must not have a budget');

        // Verify that last execution date is updated
        self::assertNotNull($periodicEntry->getLastExecutionDate());
        self::assertEquals(
            new DateTimeImmutable()->format('Y-m-d'),
            $periodicEntry->getLastExecutionDate()->format('Y-m-d')
        );
    }

    public function testAddSplitForBudgetsForecastTypeCreatesMultipleEntries(): void
    {
        $account = AccountFactory::new()->create(['name' => 'Test Provision Account']);

        $budget1 = BudgetFactory::new()->create([
            'name'    => 'Food',
            'amount'  => 60000, // 50€/month
            'enabled' => true,
        ]);

        $budget2 = BudgetFactory::new()->create([
            'name'    => 'Transportation',
            'amount'  => 36000, // 30€/month
            'enabled' => true,
        ]);

        $budget3 = BudgetFactory::new()->create([
            'name'    => 'Entertainment',
            'amount'  => 24000, // 20€/month
            'enabled' => true,
        ]);

        /** @var PeriodicEntry $periodicEntry */
        $periodicEntry = PeriodicEntryFactory::new()->create([
            'name'              => 'Monthly provisions',
            'amount'            => null, // No amount = FORECAST type
            'account'           => $account,
            'executionDate'     => new DateTimeImmutable(), // Today
            'lastExecutionDate' => null, // Never executed
            'budgets'           => [$budget1, $budget2, $budget3],
        ])->_real();

        $this->messageBus->dispatch(new AddSplitForBudgetsCommand($periodicEntry->getId()));

        /** @var Entry[] $createdEntries */
        $createdEntries = EntryFactory::repository()->findAll();

        self::assertCount(3, $createdEntries, 'Three entries must be created for the three budgets');

        // Verify amounts (budget / 12 months)
        $expectedAmounts = [
            'Monthly provisions - Food'           => 5000,  // 600/12
            'Monthly provisions - Transportation' => 3000,     // 360/12
            'Monthly provisions - Entertainment'  => 2000,        // 240/12
        ];

        foreach ($createdEntries as $entry) {
            self::assertArrayHasKey($entry->getName(), $expectedAmounts);
            self::assertEquals($expectedAmounts[$entry->getName()], $entry->getAmount());
            self::assertEquals($account->_real(), $entry->getAccount());
            self::assertEquals([EntryFlagEnum::PERIODIC_ENTRY], $entry->getFlags());
            self::assertNotNull($entry->getBudget(), 'FORECAST type entry must have a budget');
        }

        // Verify that last execution date is updated
        self::assertNotNull($periodicEntry->getLastExecutionDate());
        self::assertEquals(
            new DateTimeImmutable()->format('Y-m-d'),
            $periodicEntry->getLastExecutionDate()->format('Y-m-d')
        );
    }

    public function testAddSplitForBudgetsForecastTypeWithMixedBudgets(): void
    {
        $account = AccountFactory::new()->create(['name' => 'Test Mixed Account']);

        $activeBudget1 = BudgetFactory::new()->create([
            'name'    => 'Active Budget 1',
            'amount'  => 48000, // 40€/month
            'enabled' => true,
        ]);

        $activeBudget2 = BudgetFactory::new()->create([
            'name'    => 'Active Budget 2',
            'amount'  => 60000, // 50€/month
            'enabled' => true,
        ]);

        $inactiveBudget = BudgetFactory::new()->create([
            'name'    => 'Inactive Budget',
            'amount'  => 36000,
            'enabled' => false, // Disabled budget
        ]);

        $zeroBudget = BudgetFactory::new()->create([
            'name'    => 'Zero Budget',
            'amount'  => 0, // Zero amount
            'enabled' => true,
        ]);

        /** @var PeriodicEntry $periodicEntry */
        $periodicEntry = PeriodicEntryFactory::new()->create([
            'name'              => 'Selective provisions',
            'amount'            => null,
            'account'           => $account,
            'executionDate'     => new DateTimeImmutable(),
            'lastExecutionDate' => null,
            'budgets'           => [$activeBudget1, $activeBudget2, $inactiveBudget, $zeroBudget],
        ])->_real();

        $this->messageBus->dispatch(new AddSplitForBudgetsCommand($periodicEntry->getId()));

        /** @var Entry[] $createdEntries */
        $createdEntries = EntryFactory::repository()->findAll();

        self::assertCount(2, $createdEntries, 'Only entries for active budgets with amount > 0 must be created');

        $entryNames = array_map(fn ($entry) => $entry->getName(), $createdEntries);
        self::assertContains('Selective provisions - Active Budget 1', $entryNames);
        self::assertContains('Selective provisions - Active Budget 2', $entryNames);
        self::assertNotContains('Selective provisions - Inactive Budget', $entryNames);
        self::assertNotContains('Selective provisions - Zero Budget', $entryNames);

        foreach ($createdEntries as $entry) {
            if ('Selective provisions - Active Budget 1' === $entry->getName()) {
                self::assertEquals(4000, $entry->getAmount()); // 480/12
            } elseif ('Selective provisions - Active Budget 2' === $entry->getName()) {
                self::assertEquals(5000, $entry->getAmount()); // 600/12
            }
        }
    }

    public function testAddSplitForBudgetsTruncatesTooLongEntryNames(): void
    {
        $account = AccountFactory::new()->create(['name' => 'Test Provision Account']);
        $budget  = BudgetFactory::new()->create([
            'name'    => str_repeat('b', 200),
            'amount'  => 120000,
            'enabled' => true,
        ]);

        /** @var PeriodicEntry $periodicEntry */
        $periodicEntry = PeriodicEntryFactory::new()->create([
            'name'              => str_repeat('p', 100),
            'amount'            => null,
            'account'           => $account,
            'executionDate'     => new DateTimeImmutable(),
            'lastExecutionDate' => null,
            'budgets'           => [$budget],
        ])->_real();

        $this->messageBus->dispatch(new AddSplitForBudgetsCommand($periodicEntry->getId()));

        /** @var Entry[] $createdEntries */
        $createdEntries = EntryFactory::repository()->findAll();

        self::assertCount(1, $createdEntries);
        self::assertSame(255, mb_strlen($createdEntries[0]->getName()));
        self::assertStringStartsWith(str_repeat('p', 100) . ' - ', $createdEntries[0]->getName());
    }
}
