<?php

namespace App\Module\Exporter\Infrastructure\Document\Model;

enum DocumentTypeEnum: string
{
    case CSV = 'csv';
    case PDF = 'pdf';

    public function label(): string
    {
        return 'exporter.artifact.document_type.' . $this->name;
    }
}
