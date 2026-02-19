<?php

namespace App\Module\Exporter\Domain\Artifact\Message\Command\MarkAsErrorAllBlockedArtifact;

use App\Shared\Cqs\Message\Command\CommandInterface;

/**
 * @see MarkAsErrorBlockedArtifactHandler
 */
readonly class MarkAsErrorAllBlockedArtifactCommand implements CommandInterface
{
    public function __construct(
        private ?int $maxAgeInMinutes = null,
    ) {
    }

    public function getMaxAgeInMinutes(): ?int
    {
        return $this->maxAgeInMinutes;
    }
}
