<?php

namespace App\Tests\Integration\Module\Exporter\Domain\Artifact\Repository;

use App\Module\Exporter\Domain\Artifact\Entity\Artifact;
use App\Module\Exporter\Domain\Artifact\Entity\ArtifactStatusEnum;
use App\Module\Exporter\Domain\Artifact\Message\Query\FindArtifacts\FindArtifactsQuery;
use App\Module\Exporter\Domain\Artifact\Repository\ArtifactRepository;
use App\Tests\Factory\ArtifactFactory;
use App\Tests\Integration\Shared\KernelTestCase;
use DateMalformedStringException;
use DateTimeImmutable;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;

class ArtifactRepositoryTest extends KernelTestCase
{
    private ArtifactRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = self::getContainer()->get(ArtifactRepository::class);
    }

    public static function statusDataset(): Generator
    {
        yield 'Pending' => [
            'status'        => ArtifactStatusEnum::PENDING,
            'expectedCount' => 2,
        ];

        yield 'Done' => [
            'status'        => ArtifactStatusEnum::DONE,
            'expectedCount' => 1,
        ];
    }

    #[DataProvider('statusDataset')]
    public function testArtifactStatusAreOk(ArtifactStatusEnum $status, int $expectedCount): void
    {
        ArtifactFactory::new(['finishedAt' => null])->create();
        ArtifactFactory::new(['finishedAt' => new DateTimeImmutable()])->create();
        ArtifactFactory::new(['finishedAt' => null])->create();

        $query = new FindArtifactsQuery($status);
        /** @var Artifact[] $artifacts */
        $artifacts = $this->repository
            ->getArtifactsQueryBuilder($query)
            ->getQuery()
            ->getResult()
        ;

        self::assertCount($expectedCount, $artifacts);
        foreach ($artifacts as $artifact) {
            self::assertSame($status, $artifact->getStatus());
        }
    }

    public static function ageDataset(): Generator
    {
        yield 'Older close' => [
            'age'           => 47000,
            'initialDate'   => '2026-02-01 01:00:00',
            'expectedCount' => 1,
        ];

        yield 'Older large' => [
            'age'           => 43000,
            'initialDate'   => '2026-02-01 01:00:00',
            'expectedCount' => 2,
        ];

        yield 'Not enough older' => [
            'age'           => 80000,
            'initialDate'   => '2026-02-01 01:00:00',
            'expectedCount' => 0,
        ];
    }

    /**
     * @throws DateMalformedStringException
     */
    #[DataProvider('ageDataset')]
    public function testArtifactAge(int $age, string $initialDate, int $expectedCount): void
    {
        ArtifactFactory::new(['createdAt' => new DateTimeImmutable('2025-12-30 01:00:00')])->create();
        ArtifactFactory::new(['createdAt' => new DateTimeImmutable('2026-01-01 01:00:00')])->create();

        $query = new FindArtifactsQuery(maxAgeInMinutes: $age);
        /** @var Artifact[] $artifacts */
        $artifacts = $this->repository
            ->getArtifactsQueryBuilder($query, new DateTimeImmutable($initialDate))
            ->getQuery()
            ->getResult()
        ;

        self::assertCount($expectedCount, $artifacts);
    }

    /**
     * @throws DateMalformedStringException
     */
    public function testMarkFinished(): void
    {
        ArtifactFactory::new(['createdAt' => new DateTimeImmutable('2026-01-01 01:00:00')])->create();
        ArtifactFactory::new(['createdAt' => new DateTimeImmutable('2026-01-02 01:00:00')])->create();

        $query = new FindArtifactsQuery();
        $this->repository
            ->markAsFinishedArtifactsQueryBuilder($query)
            ->getQuery()
            ->execute();

        $artifacts = ArtifactFactory::all();
        foreach ($artifacts as $artifact) {
            self::assertNotNull($artifact->getFinishedAt());
        }
    }
}
