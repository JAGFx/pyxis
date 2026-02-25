<?php

declare(strict_types=1);

namespace App\Module\Exporter\Domain\Artifact\Entity;

enum ArtifactStatusEnum: string
{
    case PENDING  = 'pending';
    case DONE     = 'done';
    case FAILED   = 'failed';
    case DISABLED = 'disabled';

    public function label(): string
    {
        return 'exporter.artifact.status.' . $this->name;
    }

    public function color(): string
    {
        return match ($this->value) {
            self::PENDING->value  => 'black',
            self::DONE->value     => 'primary',
            self::FAILED->value   => 'secondary',
            self::DISABLED->value => 'default',
        };
    }
}
