<?php

namespace App\Module\Exporter\Infrastructure\Document\Factory;

enum DocumentTypeEnum: string
{
    case CSV = 'csv';
    case PDF = 'pdf';
}
