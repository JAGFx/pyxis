<?php

namespace App\Module\Exporter\Domain\Artifact\Message\Command\ForceFinishPendingArtifacts;

use App\Module\Exporter\Domain\Artifact\Entity\ArtifactStatusEnum;
use App\Module\Exporter\Domain\Artifact\Message\Query\FindArtifacts\FindArtifactsQuery;
use App\Module\Exporter\Domain\Artifact\Repository\ArtifactRepository;
use App\Shared\Cqs\Handler\CommandHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Throwable;

/**
 * @see ForceFinishPendingArtifactsCommand
 */
readonly class ForceFinishPendingArtifactsHandler implements CommandHandlerInterface
{
    private const int MAX_AGE_IN_MINUTES = 60 * 24; // 1 day

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ArtifactRepository $artifactRepository,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function __invoke(ForceFinishPendingArtifactsCommand $command): void
    {
        $findArtifactsQuery = new FindArtifactsQuery(ArtifactStatusEnum::PENDING, $command->getMaxAgeInMinutes() ?? self::MAX_AGE_IN_MINUTES);

        $this->artifactRepository->forceFinishPendingArtifacts($findArtifactsQuery);

        $this->entityManager->flush();
    }
}
