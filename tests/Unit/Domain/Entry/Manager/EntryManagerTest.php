<?php

namespace App\Tests\Unit\Domain\Entry\Manager;

use App\Domain\Entry\Manager\EntryManager;
use App\Domain\Entry\Message\Query\GetEntryBalanceQuery;
use App\Domain\Entry\Repository\EntryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

class EntryManagerTest extends TestCase
{
    private EntryRepository $entryRepository;
    private PaginatorInterface $paginator;

    protected function setUp(): void
    {
        $this->entryRepository = $this->createMock(EntryRepository::class);
        $this->paginator       = $this->createMock(PaginatorInterface::class);
    }

    private function createEntryManagerMock(): EntryManager|MockObject
    {
        return new EntryManager(
            $this->entryRepository,
            $this->paginator,
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(ObjectMapperInterface::class),
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
            ['id' => null, 'sum' => 150.0], // spent entries (budget = null)
            ['id' => 1, 'sum' => 300.0],    // forecast entries (budget = 1)
            ['id' => 2, 'sum' => 200.0],    // forecast entries (budget = 2)
        ];

        $this->expectBalance($expectedData);
        $result = $this->createEntryManagerMock()->balance(new GetEntryBalanceQuery());

        self::assertEquals(150.0, $result->getTotalSpent());
        self::assertEquals(500.0, $result->getTotalForecast()); // 300.0 + 200.0
    }

    public function testBalanceWithProvidedCommand(): void
    {
        $searchRequest = new GetEntryBalanceQuery();
        $expectedData  = [
            ['id' => null, 'sum' => 75.0], // spent entries
            ['id' => 1, 'sum' => 125.0],   // forecast entries
        ];

        $this->expectBalance($expectedData, $searchRequest);
        $result = $this->createEntryManagerMock()->balance($searchRequest);

        self::assertEquals(75.0, $result->getTotalSpent());
        self::assertEquals(125.0, $result->getTotalForecast());
    }

    public function testBalanceWithOnlySpentEntries(): void
    {
        $expectedData = [
            ['id' => null, 'sum' => 250.0], // only spent entries
        ];

        $this->expectBalance($expectedData);
        $result = $this->createEntryManagerMock()->balance(new GetEntryBalanceQuery());

        self::assertEquals(250.0, $result->getTotalSpent());
        self::assertEquals(0.0, $result->getTotalForecast());
    }

    public function testBalanceWithOnlyForecastEntries(): void
    {
        $expectedData = [
            ['id' => 1, 'sum' => 180.0], // only forecast entries
            ['id' => 2, 'sum' => 320.0],
        ];

        $this->expectBalance($expectedData);
        $result = $this->createEntryManagerMock()->balance(new GetEntryBalanceQuery());

        self::assertEquals(0.0, $result->getTotalSpent());
        self::assertEquals(500.0, $result->getTotalForecast()); // 180.0 + 320.0
    }

    public function testBalanceWithEmptyData(): void
    {
        $expectedData = [];

        $this->expectBalance($expectedData);
        $result = $this->createEntryManagerMock()->balance(new GetEntryBalanceQuery());

        self::assertEquals(0.0, $result->getTotalSpent());
        self::assertEquals(0.0, $result->getTotalForecast());
    }
}
