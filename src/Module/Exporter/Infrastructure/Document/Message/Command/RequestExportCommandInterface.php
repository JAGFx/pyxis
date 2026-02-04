<?php

namespace App\Module\Exporter\Infrastructure\Document\Message\Command;

use App\Module\Exporter\Infrastructure\Document\Model\DocumentTypeEnum;
use App\Module\Exporter\Infrastructure\Storage\StorageEnum;

interface RequestExportCommandInterface
{
    public function getTarget(): string;

    public function getDocumentType(): DocumentTypeEnum;

    public function getStorage(): StorageEnum;
}
