<?php

namespace App\Tests\Integration\Domain\Budget\Manager;

use App\Domain\Account\Entity\Account;
use App\Domain\Budget\DTO\BudgetAccountBalance;
use App\Domain\Budget\DTO\BudgetSearchCommand;
use App\Domain\Budget\Entity\Budget;
use App\Domain\Budget\Manager\BudgetManager;
use App\Domain\Entry\Entity\Entry;
use App\Domain\Entry\Entity\EntryKindEnum;
use App\Domain\Entry\Manager\EntryManager;
use App\Tests\Factory\AccountFactory;
use App\Tests\Factory\BudgetFactory;
use App\Tests\Factory\EntryFactory;
use App\Tests\Integration\Shared\KernelTestCase;
use DateTimeImmutable;
use Exception;

class BudgetManagerTest extends KernelTestCase
{
    private BudgetManager $budgetManager;
    private EntryManager $entryManager;
    private const string BUDGET_BALANCE_NAME = 'Budget balance';

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->budgetManager = $container->get(BudgetManager::class);
        $this->entryManager  = $container->get(EntryManager::class);
    }

    private function populateDatabase(): void
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
                'amount'    => 500,
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

    private function getBudget(array $data = []): Budget
    {
        $command = new BudgetSearchCommand();
        $command->setName($data['name'] ?? null);

        $result = $this->budgetManager->getBudgets($command);

        self::assertCount(1, $result);

        return reset($result);
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

        $initialBalance = $this->entryManager->balance();

        $this->budgetManager->balancing(new BudgetAccountBalance(
            budget: $budget,
            account: $account,
        ));

        $newBalance = $this->entryManager->balance();

        self::assertSame($initialBalance->getTotalSpent(), $newBalance->getTotalSpent());
        self::assertSame($initialBalance->getTotalForecast(), $newBalance->getTotalForecast());
    }

    public function testBudgetWithPositiveCashFlowMustTransferToSpent(): void
    {
        $this->populateDatabase();

        $initialBalance = $this->entryManager->balance();
        $overflow       = 200.0;

        $budget = $this->getBudget([
            'name' => self::BUDGET_BALANCE_NAME,
        ]);

        /** @var Account $account */
        $account = AccountFactory::first()->_real();

        $this->budgetManager->balancing(new BudgetAccountBalance(
            budget: $budget,
            account: $account,
        ));
        $newBalance = $this->entryManager->balance();

        self::assertSame($initialBalance->getTotalSpent() + $overflow, $newBalance->getTotalSpent());
        self::assertSame($initialBalance->getTotalForecast() - $overflow, $newBalance->getTotalForecast());

        /** @var Entry[] $lastTwoById */
        $lastTwoById = EntryFactory::repository()->findBy([], ['id' => 'ASC'], 2, 2);

        self::assertCount(2, $lastTwoById);

        foreach ($lastTwoById as $item) {
            self::assertStringStartsWith('Équilibrage de', $item->getName());
            self::assertSame(EntryKindEnum::BALANCING, $item->getKind());
        }

        // Test entry spent
        self::assertSame($overflow, $lastTwoById[0]->getAmount());
        self::assertNull($lastTwoById[0]->getBudget());
        self::assertSame($account, $lastTwoById[0]->getAccount());

        // Test entry forecast
        self::assertSame(-$overflow, $lastTwoById[1]->getAmount());
        self::assertSame($budget, $lastTwoById[1]->getBudget());
        self::assertSame($account, $lastTwoById[1]->getAccount());
    }
}
