<?php

namespace App\Module\Exporter\Domain\Artifact\Message\Query\DownloadArtifact;

use App\Shared\Cqs\Message\Query\QueryInterface;
use Symfony\Component\Uid\Uuid;

/**
 * @see DownloadArtifactHandler
 */
readonly class DownloadArtifactQuery implements QueryInterface
{
    public function __construct(
        private Uuid $artifactUuid,
    ) {
    }

    public function getArtifactUuid(): Uuid
    {
        return $this->artifactUuid;
    }
}
