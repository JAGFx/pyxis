<?php

namespace App\Tests\Integration\Domain\Budget\Repository;

use App\Domain\Account\Entity\Account;
use App\Domain\Budget\Manager\BudgetManager;
use App\Domain\Budget\Model\BudgetSearchCommand;
use App\Domain\Budget\ValueObject\BudgetValueObject;
use App\Shared\Utils\YearRange;
use App\Tests\Factory\AccountFactory;
use App\Tests\Factory\BudgetFactory;
use App\Tests\Factory\EntryFactory;
use App\Tests\Integration\Shared\KernelTestCase;
use DateTimeImmutable;
use Exception;

use function Zenstruck\Foundry\faker;

class BudgetRepositoryTest extends KernelTestCase
{
    private const BUDGET_NAME = 'Budget spent Current year';
    private BudgetManager $budgetManager;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->budgetManager = $container->get(BudgetManager::class);

        $this->populateDatabase();
    }

    private function populateDatabase(): void
    {
        BudgetFactory::createOne([
            'amount' => 256.0,
            'name'   => self::BUDGET_NAME,
        ]);

        /** @var Account $account */
        $account = AccountFactory::new()
            ->createOne()
            ->_real();

        EntryFactory::createMany(3, [
            'createdAt' => DateTimeImmutable::createFromMutable(faker()->dateTimeBetween('-1 year -1 month', '-1 year')),
            'amount'    => 128,
            'budget'    => BudgetFactory::find(['name' => self::BUDGET_NAME]),
            'account'   => $account,
        ]);

        EntryFactory::createMany(3, [
            'createdAt' => DateTimeImmutable::createFromMutable(faker()->dateTimeBetween('-5 hour')),
            'amount'    => -64.0,
            'budget'    => BudgetFactory::find(['name' => self::BUDGET_NAME]),
            'account'   => $account,
        ]);

        EntryFactory::createMany(2, [
            'createdAt' => DateTimeImmutable::createFromMutable(faker()->dateTimeBetween('-5 hour')),
            'amount'    => 64.0,
            'budget'    => BudgetFactory::find(['name' => self::BUDGET_NAME]),
            'account'   => $account,
        ]);
    }

    private function getBudgetVos(array $data = []): array
    {
        $command = (new BudgetSearchCommand());
        $command
            ->setName(self::BUDGET_NAME)
            ->setYear($data['year'] ?? null)
            ->setShowCredits($data['showCredit'] ?? null);

        $budgetsVos = $this->budgetManager->getBudgetValuesObject($command);

        self::assertCount(1, $budgetsVos);

        return $budgetsVos;
    }

    public function testBudgetSpentProgressForSpecificYearIsCorrect(): void
    {
        $budgetsVos = $this->getBudgetVos([
            'year'       => YearRange::current(),
            'showCredit' => false,
        ]);
        $budgetsVo = reset($budgetsVos);

        self::assertInstanceOf(BudgetValueObject::class, $budgetsVo);
        self::assertSame(self::BUDGET_NAME, $budgetsVo->getName());
        self::assertSame(256.0, $budgetsVo->getAmount());
        self::assertSame(-192.0, $budgetsVo->getProgress());
    }

    public function testBudgetForecastProgressForSpecificYearIsCorrect(): void
    {
        $budgetsVos = $this->getBudgetVos([
            'year'       => YearRange::current(),
            'showCredit' => true,
        ]);

        $budgetsVo = reset($budgetsVos);

        self::assertInstanceOf(BudgetValueObject::class, $budgetsVo);
        self::assertSame(self::BUDGET_NAME, $budgetsVo->getName());
        self::assertSame(256.0, $budgetsVo->getAmount());
        self::assertSame(128.0, $budgetsVo->getProgress());
    }

    public function testBudgetSoldForSpecificYearIsCorrect(): void
    {
        $budgetsVos = $this->getBudgetVos([
            'year' => YearRange::current(),
        ]);

        $budgetsVo = reset($budgetsVos);

        self::assertInstanceOf(BudgetValueObject::class, $budgetsVo);
        self::assertSame(self::BUDGET_NAME, $budgetsVo->getName());
        self::assertSame(256.0, $budgetsVo->getAmount());
        self::assertSame(-64.0, $budgetsVo->getProgress());
    }

    public function testBudgetSoldIsCorrect(): void
    {
        $budgetsVos = $this->getBudgetVos();

        $budgetsVo = reset($budgetsVos);

        self::assertInstanceOf(BudgetValueObject::class, $budgetsVo);
        self::assertSame(self::BUDGET_NAME, $budgetsVo->getName());
        self::assertSame(256.0, $budgetsVo->getAmount());
        self::assertSame(320.0, $budgetsVo->getProgress());
    }
}
