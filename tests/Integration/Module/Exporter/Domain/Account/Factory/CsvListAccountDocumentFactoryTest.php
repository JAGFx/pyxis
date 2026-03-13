<?php

namespace App\Tests\Integration\Module\Exporter\Domain\Account\Factory;

use App\Module\Exporter\Domain\Account\Factory\CsvListAccountDocumentFactory;
use App\Module\Exporter\Domain\Account\Message\Command\RequestExportAccountList\RequestExportAccountListCommand;
use App\Tests\Integration\Shared\KernelTestCase;

class CsvListAccountDocumentFactoryTest extends KernelTestCase
{
    private CsvListAccountDocumentFactory $csvListAccountDocumentFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $container = self::getContainer();

        $this->csvListAccountDocumentFactory = $container->get(CsvListAccountDocumentFactory::class);
    }

    public function testRawDataHeaders(): void
    {
        [$headers] = $this->csvListAccountDocumentFactory->getRawData(new RequestExportAccountListCommand());

        self::assertCount(2, $headers);
        self::assertEquals(['ID', 'Libellé'], $headers);
    }
}
