<?php

namespace App\Module\Exporter\Domain\Artifact\Entity;

enum ArtifiactStatusEnum: string
{
    case PENDING = 'pending';
    case DONE    = 'done';
}
