<?php

namespace App\Module\Exporter\Infrastructure\Document\Model;

enum DocumentTypeEnum: string
{
    case CSV = 'csv';
    case PDF = 'pdf';
}
