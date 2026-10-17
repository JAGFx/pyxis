<?php

namespace App\Tests\Integration\Domain\PeriodicEntry\Repository;

use App\Domain\Entry\Entity\EntryTypeEnum;
use App\Domain\PeriodicEntry\Entity\PeriodicEntry;
use App\Domain\PeriodicEntry\Message\Query\FindPeriodicEntries\FindPeriodicEntriesQuery;
use App\Domain\PeriodicEntry\Repository\PeriodicEntryRepository;
use App\Tests\Factory\AccountFactory;
use App\Tests\Factory\BudgetFactory;
use App\Tests\Factory\PeriodicEntryFactory;
use App\Tests\Integration\Shared\KernelTestCase;

class PeriodicEntryRepositoryTest extends KernelTestCase
{
    private PeriodicEntryRepository $periodicEntryRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->periodicEntryRepository = $container->get(PeriodicEntryRepository::class);

        $this->populateDatabase();
    }

    private function populateDatabase(): void
    {
        PeriodicEntryFactory::createSequence([
            [
                'amount'  => 4200,
                'name'    => 'Spent',
                'account' => AccountFactory::new()->create(),
            ],
            [
                'amount'  => null,
                'name'    => 'Forecast',
                'budgets' => BudgetFactory::createSequence([
                    ['amount' => 12300],
                    ['amount' => 45600],
                    ['amount' => 78900, 'enabled' => false],
                ]),
                'account' => AccountFactory::new()->create(),
            ],
        ]);
    }

    public function testGetPeriodicEntriesVosIsRightClass(): void
    {
        $periodicEntries = $this->periodicEntryRepository
            ->getPeriodicEntriesQueryBuilder()
            ->getQuery()
            ->getResult()
        ;

        foreach ($periodicEntries as $periodicEntry) {
            self::assertInstanceOf(PeriodicEntry::class, $periodicEntry);
        }
    }

    public function testPeriodicEntriesSpentIsRight(): void
    {
        $periodicEntriesSpent = $this->periodicEntryRepository
            ->getPeriodicEntriesQueryBuilder(
                new FindPeriodicEntriesQuery(EntryTypeEnum::TYPE_SPENT)
            )
            ->getQuery()
            ->getResult()
        ;

        self::assertCount(1, $periodicEntriesSpent);

        /** @var PeriodicEntry $periodicEntrySpent */
        $periodicEntrySpent = reset($periodicEntriesSpent);
        self::assertSame('Spent', $periodicEntrySpent->getName());
        self::assertSame(4200, $periodicEntrySpent->getAmount());
        self::assertSame(EntryTypeEnum::TYPE_SPENT, $periodicEntrySpent->getType());
        self::assertTrue($periodicEntrySpent->isSpent());
    }

    public function testPeriodicEntriesForecastIsRight(): void
    {
        $periodicEntriesSpent = $this->periodicEntryRepository
            ->getPeriodicEntriesQueryBuilder(
                new FindPeriodicEntriesQuery(EntryTypeEnum::TYPE_FORECAST)
            )
            ->getQuery()
            ->getResult()
        ;

        self::assertCount(1, $periodicEntriesSpent);

        /** @var PeriodicEntry $periodicEntrySpent */
        $periodicEntrySpent = reset($periodicEntriesSpent);
        self::assertSame('Forecast', $periodicEntrySpent->getName());
        self::assertSame(4825, $periodicEntrySpent->getTotalAmount());
        self::assertSame(EntryTypeEnum::TYPE_FORECAST, $periodicEntrySpent->getType());
        self::assertTrue($periodicEntrySpent->isForecast());
    }
}
