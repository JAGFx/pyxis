<?php

namespace App\Module\Exporter\Infrastructure\Document\Message\Query;

use App\Module\Exporter\Infrastructure\Document\Model\DocumentTypeEnum;
use App\Module\Exporter\Infrastructure\Storage\StorageEnum;

interface ExporterQueryInterface
{
    public function getTarget(): string;

    public function getDocumentType(): DocumentTypeEnum;

    public function getStorage(): StorageEnum;
}
