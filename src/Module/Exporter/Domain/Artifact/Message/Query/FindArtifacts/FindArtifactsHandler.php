<?php

namespace App\Module\Exporter\Domain\Artifact\Message\Query\FindArtifacts;

use App\Module\Exporter\Domain\Artifact\Entity\Artifact;
use App\Module\Exporter\Domain\Artifact\Repository\ArtifactRepository;
use App\Shared\Cqs\Handler\QueryHandlerInterface;

/**
 * @see FindArtifactsQuery
 */
readonly class FindArtifactsHandler implements QueryHandlerInterface
{
    public function __construct(
        private ArtifactRepository $artifactRepository,
    ) {
    }

    /**
     * @return Artifact[]
     */
    public function __invoke(FindArtifactsQuery $query): array
    {
        /**
         * @var Artifact[] $artifacts
         */
        $artifacts = $this->artifactRepository
            ->getArtifactsQueryBuilder($query)
            ->getQuery()
            ->getResult()
        ;

        return $artifacts;
    }
}
