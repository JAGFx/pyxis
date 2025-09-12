<?php

namespace App\Tests\Integration\Domain\Budget\Repository;

use App\Domain\Budget\Repository\HistoryBudgetRepository;
use App\Tests\Factory\HistoryBudgetFactory;
use App\Tests\Integration\Shared\KernelTestCase;
use DateTime;

class HistoryBudgetRepositoryTest extends KernelTestCase
{
    private HistoryBudgetRepository $historyBudgetRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->historyBudgetRepository = $container->get(HistoryBudgetRepository::class);

        $this->populateDatabase();
    }

    private function populateDatabase(): void
    {
        // Create entries for 2023 (multiple entries to test DISTINCT)
        HistoryBudgetFactory::new()->create(['date' => new DateTime('2023-01-15')]);
        HistoryBudgetFactory::new()->create(['date' => new DateTime('2023-06-20')]);
        HistoryBudgetFactory::new()->create(['date' => new DateTime('2023-12-31')]);

        // Create entries for 2024
        HistoryBudgetFactory::new()->create(['date' => new DateTime('2024-03-10')]);
        HistoryBudgetFactory::new()->create(['date' => new DateTime('2024-08-25')]);

        // Create entries for 2022
        HistoryBudgetFactory::new()->create(['date' => new DateTime('2022-05-12')]);

        // Create entry for 2025
        HistoryBudgetFactory::new()->create(['date' => new DateTime('2025-01-01')]);
    }

    public function testGetAvailableYear(): void
    {
        $queryBuilder = $this->historyBudgetRepository->getAvailableYear();
        $results      = $queryBuilder->getQuery()->getResult();

        $years = array_map(fn ($row) => (int) $row['year'], $results);

        $this->assertCount(4, $results, 'Should return 4 distinct years');
        $this->assertEquals([2025, 2024, 2023, 2022], $years, 'Years should be in descending order');
    }

    public function testGetAvailableYearWithNoData(): void
    {
        // Clear all data
        $this->historyBudgetRepository->createQueryBuilder('hb')->delete()->getQuery()->execute();

        $queryBuilder = $this->historyBudgetRepository->getAvailableYear();
        $results      = $queryBuilder->getQuery()->getResult();

        $this->assertEmpty($results, 'Should return empty array when no data exists');
    }
}
