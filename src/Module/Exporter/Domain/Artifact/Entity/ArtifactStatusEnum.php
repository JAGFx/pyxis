<?php

namespace App\Module\Exporter\Domain\Artifact\Entity;

enum ArtifactStatusEnum: string
{
    case PENDING  = 'pending';
    case DONE     = 'done';
    case FAILED   = 'failed';
    case DISABLED = 'disabled';
}
