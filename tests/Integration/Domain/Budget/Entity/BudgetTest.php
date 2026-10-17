<?php

namespace App\Tests\Integration\Domain\Budget\Entity;

use App\Domain\Account\Entity\Account;
use App\Domain\Budget\Entity\Budget;
use App\Tests\Factory\AccountFactory;
use App\Tests\Factory\BudgetFactory;
use App\Tests\Factory\EntryFactory;
use App\Tests\Integration\Shared\KernelTestCase;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class BudgetTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testEntriesCreatedAtTheSameSecondAreAllHydrated(): void
    {
        /** @var Budget $budget */
        $budget = BudgetFactory::createOne(['amount' => 1200.0])->_real();
        /** @var Account $account */
        $account   = AccountFactory::createOne()->_real();
        $createdAt = new DateTimeImmutable('-1 year')->setTime(14, 0);

        EntryFactory::createSequence([
            ['createdAt' => $createdAt, 'amount' => 100.0, 'budget' => $budget, 'account' => $account],
            ['createdAt' => $createdAt, 'amount' => 100.0, 'budget' => $budget, 'account' => $account],
            ['createdAt' => $createdAt, 'amount' => 100.0, 'budget' => $budget, 'account' => $account],
        ]);

        $budgetId = $budget->getId();
        $this->entityManager->clear();

        /** @var Budget $budget */
        $budget = $this->entityManager->find(Budget::class, $budgetId);

        self::assertCount(3, $budget->getEntries());
        self::assertSame(300.0, $budget->getCashFlow());
    }

    public function testOppositeCashFlowsOnDifferentAccountsMustBeBalanced(): void
    {
        /** @var Budget $budget */
        $budget = BudgetFactory::createOne(['amount' => 1200.0])->_real();
        /** @var Account $firstAccount */
        $firstAccount = AccountFactory::createOne()->_real();
        /** @var Account $secondAccount */
        $secondAccount = AccountFactory::createOne()->_real();

        EntryFactory::createSequence([
            ['createdAt' => new DateTimeImmutable('-1 year'), 'amount' => 50.0, 'budget' => $budget, 'account' => $firstAccount],
            ['createdAt' => new DateTimeImmutable('-1 year -1 hour'), 'amount' => -50.0, 'budget' => $budget, 'account' => $secondAccount],
        ]);

        self::assertFalse($budget->hasPositiveCashFlow());
        self::assertFalse($budget->hasNegativeCashFlow());
        self::assertTrue($budget->hasCashFlowToBalance());
    }

    public function testBalancedAccountsHaveNoCashFlowToBalance(): void
    {
        /** @var Budget $budget */
        $budget = BudgetFactory::createOne(['amount' => 1200.0])->_real();
        /** @var Account $account */
        $account = AccountFactory::createOne()->_real();

        EntryFactory::createSequence([
            ['createdAt' => new DateTimeImmutable('-1 year'), 'amount' => 0.1, 'budget' => $budget, 'account' => $account],
            ['createdAt' => new DateTimeImmutable('-1 year -1 hour'), 'amount' => 0.2, 'budget' => $budget, 'account' => $account],
            ['createdAt' => new DateTimeImmutable('-1 year -2 hour'), 'amount' => -0.3, 'budget' => $budget, 'account' => $account],
        ]);

        self::assertFalse($budget->hasCashFlowToBalance());
    }
}
