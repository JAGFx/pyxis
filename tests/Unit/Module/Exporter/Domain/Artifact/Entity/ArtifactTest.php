<?php

namespace App\Tests\Unit\Module\Exporter\Domain\Artifact\Entity;

use App\Module\Exporter\Domain\Artifact\Entity\Artifact;
use App\Module\Exporter\Domain\Artifact\Entity\ArtifiactStatusEnum;
use DateTimeImmutable;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ArtifactTest extends TestCase
{
    private function generateArtifact(array $data = []): Artifact
    {
        $artifact = new Artifact('export_command');

        if (isset($data['finishedAt'])) {
            $artifact->setFinishedAt($data['finishedAt']);
        }

        return $artifact;
    }

    public static function statusGenerator(): Generator
    {
        yield 'Pending' => [
            'data'           => [],
            'expectedStatus' => ArtifiactStatusEnum::PENDING,
        ];
        yield 'Finished' => [
            'data' => [
                'finishedAt' => new DateTimeImmutable(),
            ],
            'expectedStatus' => ArtifiactStatusEnum::DONE,
        ];
    }

    #[DataProvider('statusGenerator')]
    public function testStatus(array $data, ArtifiactStatusEnum $expectedStatus): void
    {
        $artifact = $this->generateArtifact($data);
        self::assertSame($expectedStatus, $artifact->getStatus());
    }
}
