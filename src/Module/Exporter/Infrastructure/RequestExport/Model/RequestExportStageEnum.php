<?php

namespace App\Module\Exporter\Infrastructure\RequestExport\Model;

enum RequestExportStageEnum: string
{
    case ARTIFACT_CREATION = 'artifact_creation';
    case EXPORTING         = 'exporting';
}
