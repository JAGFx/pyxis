<?php

namespace App\Tests\Unit\Domain\Entry\Message\Query;

use App\Domain\Entry\Message\Query\GetEntryBalance\GetEntryBalanceHandler;
use App\Domain\Entry\Message\Query\GetEntryBalance\GetEntryBalanceQuery;
use App\Domain\Entry\Repository\EntryRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetEntryBalanceHandlerTest extends TestCase
{
    private EntryRepository $entryRepository;

    protected function setUp(): void
    {
        $this->entryRepository = $this->createMock(EntryRepository::class);
    }

    private function createGetEntryBalanceHandlerMock(): GetEntryBalanceHandler|MockObject
    {
        return new GetEntryBalanceHandler(
            $this->entryRepository,
        );
    }

    private function expectBalance(array $expectedData, ?GetEntryBalanceQuery $getEntryBalanceQuery = null): void
    {
        $query = $this->createMock(Query::class);
        $query->expects(self::once())
            ->method('getResult')
            ->willReturn($expectedData);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects(self::once())
            ->method('getQuery')
            ->willReturn($query);

        $this->entryRepository
            ->expects(self::once())
            ->method('balance')
            ->with($getEntryBalanceQuery ?? new GetEntryBalanceQuery())
            ->willReturn($queryBuilder);
    }

    public function testBalanceWithNullCommand(): void
    {
        $expectedData = [
            ['id' => null, 'sum' => 15000], // spent entries (budget = null)
            ['id' => 1, 'sum' => 30000],    // forecast entries (budget = 1)
            ['id' => 2, 'sum' => 20000],    // forecast entries (budget = 2)
        ];

        $this->expectBalance($expectedData);
        $result = $this->createGetEntryBalanceHandlerMock()->__invoke(new GetEntryBalanceQuery());

        self::assertEquals(15000, $result->getTotalSpent());
        self::assertEquals(50000, $result->getTotalForecast()); // 30000 + 20000
    }

    public function testBalanceWithProvidedCommand(): void
    {
        $searchRequest = new GetEntryBalanceQuery();
        $expectedData  = [
            ['id' => null, 'sum' => 7500], // spent entries
            ['id' => 1, 'sum' => 12500],   // forecast entries
        ];

        $this->expectBalance($expectedData, $searchRequest);
        $result = $this->createGetEntryBalanceHandlerMock()->__invoke($searchRequest);

        self::assertEquals(7500, $result->getTotalSpent());
        self::assertEquals(12500, $result->getTotalForecast());
    }

    public function testBalanceWithOnlySpentEntries(): void
    {
        $expectedData = [
            ['id' => null, 'sum' => 25000], // only spent entries
        ];

        $this->expectBalance($expectedData);
        $result = $this->createGetEntryBalanceHandlerMock()->__invoke(new GetEntryBalanceQuery());

        self::assertEquals(25000, $result->getTotalSpent());
        self::assertEquals(0, $result->getTotalForecast());
    }

    public function testBalanceWithOnlyForecastEntries(): void
    {
        $expectedData = [
            ['id' => 1, 'sum' => 18000], // only forecast entries
            ['id' => 2, 'sum' => 32000],
        ];

        $this->expectBalance($expectedData);
        $result = $this->createGetEntryBalanceHandlerMock()->__invoke(new GetEntryBalanceQuery());

        self::assertEquals(0, $result->getTotalSpent());
        self::assertEquals(50000, $result->getTotalForecast()); // 18000 + 32000
    }

    public function testBalanceWithEmptyData(): void
    {
        $expectedData = [];

        $this->expectBalance($expectedData);
        $result = $this->createGetEntryBalanceHandlerMock()->__invoke(new GetEntryBalanceQuery());

        self::assertEquals(0, $result->getTotalSpent());
        self::assertEquals(0, $result->getTotalForecast());
    }
}
