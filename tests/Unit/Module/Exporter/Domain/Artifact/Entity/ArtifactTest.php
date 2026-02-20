<?php

namespace App\Tests\Unit\Module\Exporter\Domain\Artifact\Entity;

use App\Module\Exporter\Domain\Artifact\Entity\Artifact;
use App\Module\Exporter\Domain\Artifact\Entity\ArtifactStatusEnum;
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

        if (isset($data['disabledAt'])) {
            $artifact->setDisabledAt($data['disabledAt']);
        }

        if (isset($data['documentPath'])) {
            $artifact->setDocumentPath($data['documentPath']);
        }

        return $artifact;
    }

    public static function statusGenerator(): Generator
    {
        yield 'Pending' => [
            'data'           => [],
            'expectedStatus' => ArtifactStatusEnum::PENDING,
        ];
        yield 'Finished' => [
            'data' => [
                'finishedAt'   => new DateTimeImmutable(),
                'documentPath' => 'path/to/document',
            ],
            'expectedStatus' => ArtifactStatusEnum::DONE,
        ];
        yield 'Disabled' => [
            'data' => [
                'finishedAt' => new DateTimeImmutable(),
                'disabledAt' => new DateTimeImmutable(),
            ],
            'expectedStatus' => ArtifactStatusEnum::DISABLED,
        ];
        yield 'Failed' => [
            'data' => [
                'finishedAt' => new DateTimeImmutable(),
            ],
            'expectedStatus' => ArtifactStatusEnum::FAILED,
        ];
    }

    #[DataProvider('statusGenerator')]
    public function testStatus(array $data, ArtifactStatusEnum $expectedStatus): void
    {
        $artifact = $this->generateArtifact($data);
        self::assertSame($expectedStatus, $artifact->getStatus());
    }
}
