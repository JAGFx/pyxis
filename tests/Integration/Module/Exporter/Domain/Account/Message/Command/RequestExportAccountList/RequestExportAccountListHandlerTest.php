<?php

namespace App\Tests\Integration\Module\Exporter\Domain\Account\Message\Command\RequestExportAccountList;

use App\Module\Exporter\Domain\Account\Message\Command\RequestExportAccountList\RequestExportAccountListCommand;
use App\Module\Exporter\Domain\Artifact\Entity\Artifact;
use App\Module\Exporter\Infrastructure\Document\Model\DocumentTypeEnum;
use App\Module\Exporter\Infrastructure\Storage\StorageEnum;
use App\Tests\Factory\ArtifactFactory;
use App\Tests\Integration\Shared\CommandHandlerTestCase;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Throwable;

class RequestExportAccountListHandlerTest extends CommandHandlerTestCase
{
    /**
     * @throws Throwable
     * @throws ExceptionInterface
     */
    public function testHandlerPassSuccessfully(): void
    {
        $command = new RequestExportAccountListCommand(DocumentTypeEnum::CSV, StorageEnum::FILE_SYSTEM);

        $this->messageBus->dispatch($command);
        $this->assertAsyncDispatchSuccessfully();

        self::assertSame(1, ArtifactFactory::count());
        /** @var Artifact $firstArtifact */
        $firstArtifact = ArtifactFactory::first()->_real();
        $this->assertArtifactIsFinished($firstArtifact, 'export_account_list');

        self::assertEmailCount(1);
    }
}
