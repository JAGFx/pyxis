<?php

namespace App\Module\Exporter\Domain\Artifact\Message\Command\DisableOldestArtifacts;

use App\Shared\Cqs\Message\Command\CommandInterface;

/**
 * @see DisableOldestArtifactsHandler
 */
readonly class DisableOldestArtifactsCommand implements CommandInterface
{
    public function __construct(
        private ?int $maxAgeInDays = null,
    ) {
    }

    public function getMaxAgeInDays(): ?int
    {
        return $this->maxAgeInDays;
    }
}
