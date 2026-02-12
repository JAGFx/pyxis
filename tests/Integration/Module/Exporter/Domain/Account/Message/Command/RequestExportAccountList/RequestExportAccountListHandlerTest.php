<?php

namespace App\Tests\Integration\Module\Exporter\Domain\Account\Message\Command\RequestExportAccountList;

use App\Module\Exporter\Domain\Account\Message\Command\RequestExportAccountList\RequestExportAccountListCommand;
use App\Module\Exporter\Infrastructure\Document\Model\DocumentTypeEnum;
use App\Module\Exporter\Infrastructure\Storage\StorageEnum;
use App\Tests\Integration\Shared\CommandHandlerTestCase;

class RequestExportAccountListHandlerTest extends CommandHandlerTestCase
{
    public function testHandlerPassSuccessfully(): void
    {
        $command = new RequestExportAccountListCommand(DocumentTypeEnum::CSV, StorageEnum::FILE_SYSTEM);

        $this->assertDispatchSuccessfully($command);
        $this->assertArtifactExist('request_export_account_list_command');
        self::assertEmailCount(1);
    }
}
