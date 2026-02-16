<?php

namespace App\Tests\Unit\Module\Exporter\Infrastructure\RequestExport\Message\Command;

use App\Module\Exporter\Domain\Account\Message\Command\RequestExportAccountList\RequestExportAccountListCommand;
use App\Module\Exporter\Infrastructure\Document\Model\DocumentTypeEnum;
use App\Module\Exporter\Infrastructure\RequestExport\Model\RequestExportStageEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class AbstractRequestExportCommandTest extends TestCase
{
    public function testStageArtifactCreation(): void
    {
        $exportCommand = new RequestExportAccountListCommand(DocumentTypeEnum::CSV);

        self::assertSame(RequestExportStageEnum::ARTIFACT_CREATION, $exportCommand->getStage());
        self::assertFalse($exportCommand->isOnExportingStage());
    }

    public function testStageArtifactExporting(): void
    {
        $exportCommand = new RequestExportAccountListCommand(DocumentTypeEnum::CSV)->setParentArtifactUuid(Uuid::v7());

        self::assertSame(RequestExportStageEnum::EXPORTING, $exportCommand->getStage());
        self::assertTrue($exportCommand->isOnExportingStage());
    }
}
