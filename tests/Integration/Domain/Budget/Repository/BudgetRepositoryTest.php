<?php

namespace App\Tests\Integration\Domain\Budget\Repository;

use App\Domain\Account\Entity\Account;
use App\Domain\Budget\Message\Query\BudgetSearchQuery;
use App\Domain\Budget\Repository\BudgetRepository;
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
    private const string BUDGET_NAME = 'Budget spent Current year';
    private BudgetRepository $budgetRepository;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->budgetRepository = $container->get(BudgetRepository::class);

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
        $searchQuery = new BudgetSearchQuery()
            ->setName(self::BUDGET_NAME)
            ->setYear($data['year'] ?? null)
            ->setShowCredits($data['showCredit'] ?? null);

        $budgetsVos = $this->budgetRepository
            ->getBudgetValueObjectsQueryBuilder($searchQuery)
            ->getQuery()
            ->getResult()
        ;

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
