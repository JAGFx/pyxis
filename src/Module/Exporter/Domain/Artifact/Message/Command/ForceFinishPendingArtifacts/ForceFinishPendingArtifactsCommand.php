<?php

namespace App\Module\Exporter\Domain\Artifact\Message\Command\ForceFinishPendingArtifacts;

use App\Shared\Cqs\Message\Command\CommandInterface;

/**
 * @see MarkAsErrorBlockedArtifactHandler
 */
readonly class ForceFinishPendingArtifactsCommand implements CommandInterface
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
