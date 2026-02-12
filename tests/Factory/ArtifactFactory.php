<?php

namespace App\Tests\Factory;

use App\Module\Exporter\Domain\Artifact\Entity\Artifact;
use Symfony\Component\Uid\Uuid;
use Zenstruck\Foundry\ModelFactory;

// TODO use the other model. Current is deprecated
final class ArtifactFactory extends ModelFactory
{
    protected static function getClass(): string
    {
        return Artifact::class;
    }

    protected function getDefaults(): array
    {
        return [
            'command' => self::faker()->word(),
            'uuid'    => Uuid::v7(),
        ];
    }
}
