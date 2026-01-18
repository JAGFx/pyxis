<?php

namespace App\Module\Exporter\Infrastructure\Document\Message\Query;

use App\Module\Exporter\Infrastructure\Document\Factory\DocumentTypeEnum;

interface ExporterQueryInterface
{
    public function getTarget(): string;

    public function getDocumentType(): DocumentTypeEnum;
}
