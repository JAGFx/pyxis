<?php

namespace App\Module\Exporter\Domain\Artifact\Message\Query\FindArtifacts;

use App\Module\Exporter\Domain\Artifact\Entity\ArtifactStatusEnum;
use App\Shared\Cqs\Message\Query\QueryInterface;

/**
 * @see FindArtifactsHandler
 */
class FindArtifactsQuery implements QueryInterface
{
    public function __construct(
        private ?ArtifactStatusEnum $status = null,
        private ?int                $maxAgeInMinutes = null,
    ) {
    }

    public function getStatus(): ?ArtifactStatusEnum
    {
        return $this->status;
    }

    public function setStatus(?ArtifactStatusEnum $status): FindArtifactsQuery
    {
        $this->status = $status;

        return $this;
    }

    public function getMaxAgeInMinutes(): ?int
    {
        return $this->maxAgeInMinutes;
    }

    public function setMaxAgeInMinutes(?int $maxAgeInMinutes): FindArtifactsQuery
    {
        $this->maxAgeInMinutes = $maxAgeInMinutes;

        return $this;
    }
}
