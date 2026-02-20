<?php

namespace App\Module\Exporter\Domain\Artifact\Message\Command\DisableOldestArtifacts;

use App\Module\Exporter\Domain\Artifact\Entity\ArtifactStatusEnum;
use App\Module\Exporter\Domain\Artifact\Message\Query\FindArtifacts\FindArtifactsQuery;
use App\Module\Exporter\Domain\Artifact\Repository\ArtifactRepository;
use App\Module\Exporter\Infrastructure\Storage\StorageSystem;
use App\Shared\Cqs\Handler\CommandHandlerInterface;
use DateMalformedStringException;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemException;
use Psr\Log\LoggerInterface;

/**
 * @see DisableOldestArtifactsCommand
 */
readonly class DisableOldestArtifactsHandler implements CommandHandlerInterface
{
    public const int DEFAULT_MAX_AGE_IN_DAYS = 30;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ArtifactRepository $artifactRepository,
        private StorageSystem $storageSystem,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws DateMalformedStringException
     */
    public function __invoke(DisableOldestArtifactsCommand $command): void
    {
        $findArtifactsQuery      = new FindArtifactsQuery(ArtifactStatusEnum::DONE, ($command->getMaxAgeInDays() ?? self::DEFAULT_MAX_AGE_IN_DAYS) * 60 * 24);
        $oldestArtifactDocuments = $this->artifactRepository->getOldestArtifactDocuments($findArtifactsQuery);

        foreach ($oldestArtifactDocuments as $oldestArtifactDocument) {
            try {
                $this->storageSystem->delete($oldestArtifactDocument);
            } catch (FilesystemException $exception) {
                $this->logger->error('[DisableOldestArtifactsHandler] Unable to delete artifact document', [
                    'documentPath' => $oldestArtifactDocument->getPath(),
                    'error' => $exception->getMessage()
                ]);
            }
        }

        $this->artifactRepository->disableOldestArtifacts($findArtifactsQuery);
        $this->entityManager->flush();
    }
}
