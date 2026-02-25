<?php

declare(strict_types=1);

namespace App\Module\Exporter\Domain\Artifact\Message\Query\FindArtifacts;

use App\Module\Exporter\Domain\Artifact\Entity\Artifact;
use App\Module\Exporter\Domain\Artifact\Repository\ArtifactRepository;
use App\Shared\Cqs\Handler\QueryHandlerInterface;
use DateMalformedStringException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @see FindArtifactsQuery
 */
readonly class FindArtifactsHandler implements QueryHandlerInterface
{
    public function __construct(
        private ArtifactRepository $artifactRepository,
        private PaginatorInterface $paginator,
    ) {
    }

    /**
     * @return PaginationInterface<int, Artifact>
     *
     * @throws DateMalformedStringException
     */
    public function __invoke(FindArtifactsQuery $query): PaginationInterface
    {
        /** @var PaginationInterface<int, Artifact> $pagination */
        $pagination = $this->paginator->paginate(
            $this->artifactRepository->getArtifactsQueryBuilder($query),
            $query->getPage(),
            $query->getPageSize()
        );

        return $pagination;
    }
}
