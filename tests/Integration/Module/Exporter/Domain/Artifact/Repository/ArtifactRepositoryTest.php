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

        yield 'Disabled' => [
            'status'        => ArtifactStatusEnum::DISABLED,
            'expectedCount' => 1,
        ];

        yield 'Failed' => [
            'status'        => ArtifactStatusEnum::FAILED,
            'expectedCount' => 1,
        ];
    }

    #[DataProvider('statusDataset')]
    public function testArtifactStatusAreOk(ArtifactStatusEnum $status, int $expectedCount): void
    {
        ArtifactFactory::new(['finishedAt' => null,                    'disabledAt' => null])->create();                                              // Pending
        ArtifactFactory::new(['finishedAt' => null,                    'disabledAt' => new DateTimeImmutable()])->create();                           // Pending (Invalid disabledAt)
        ArtifactFactory::new(['finishedAt' => new DateTimeImmutable(), 'disabledAt' => null,                    'documentPath' => 'local://path/to/document'])->create(); // Done
        ArtifactFactory::new(['finishedAt' => new DateTimeImmutable(), 'disabledAt' => new DateTimeImmutable()])->create();                           // Disabled
        ArtifactFactory::new(['finishedAt' => new DateTimeImmutable(), 'disabledAt' => null,                    'documentPath' => null])->create();   // Failed

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
    public function testForceFinishPendingArtifacts(): void
    {
        ArtifactFactory::new(['createdAt' => new DateTimeImmutable('2026-01-01 01:00:00')])->create();
        ArtifactFactory::new(['createdAt' => new DateTimeImmutable('2026-01-02 01:00:00')])->create();

        $query = new FindArtifactsQuery();
        $this->repository->forceFinishPendingArtifacts($query);

        /** @var Artifact[] $artifacts */
        $artifacts = ArtifactFactory::all();
        foreach ($artifacts as $artifact) {
            self::assertNotNull($artifact->getFinishedAt());
        }
    }

    /**
     * @throws DateMalformedStringException
     */
    public function testDisableOldestArtifacts(): void
    {
        ArtifactFactory::new(['createdAt' => new DateTimeImmutable('2026-01-01 01:00:00'), 'finishedAt' => new DateTimeImmutable(), 'documentPath' => 'local://path/to/document'])->create();
        ArtifactFactory::new(['createdAt' => new DateTimeImmutable('2026-01-02 01:00:00'), 'finishedAt' => new DateTimeImmutable(), 'documentPath' => 'local://path/to/document'])->create();

        $query = new FindArtifactsQuery(ArtifactStatusEnum::DONE);
        $this->repository->disableOldestArtifacts($query);

        /** @var Artifact[] $artifacts */
        $artifacts = ArtifactFactory::all();
        foreach ($artifacts as $artifact) {
            self::assertNotNull($artifact->getDisabledAt());
        }
    }
}
