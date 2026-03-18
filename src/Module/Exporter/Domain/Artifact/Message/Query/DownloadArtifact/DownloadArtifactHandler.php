<?php

namespace App\Module\Exporter\Domain\Artifact\Message\Query\DownloadArtifact;

use App\Infrastructure\Cqs\Security\AuthorizationChecker;
use App\Infrastructure\Doctrine\Service\EntityFinder;
use App\Module\Exporter\Domain\Artifact\Entity\Artifact;
use App\Module\Exporter\Domain\Artifact\Security\ArtifactVoter;
use App\Module\Exporter\Infrastructure\Document\Model\Document;
use App\Module\Exporter\Infrastructure\Storage\StorageSystem;
use App\Shared\Cqs\Handler\QueryHandlerInterface;
use ReflectionException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @see DownloadArtifactQuery
 */
readonly class DownloadArtifactHandler implements QueryHandlerInterface
{
    public function __construct(
        private StorageSystem $storage,
        private EntityFinder $entityFinder,
        private AuthorizationChecker $authorizationChecker,
    ) {
    }

    /**
     * @throws ReflectionException
     */
    public function __invoke(DownloadArtifactQuery $query): StreamedResponse
    {
        $artifact = $this
            ->entityFinder
            ->findByUuidIdentifierOrFail(Artifact::class, $query->getArtifactUuid());

        $this->authorizationChecker->denyAccessUnlessGranted(
            ArtifactVoter::DOWNLOAD,
            $artifact
        );

        $document = new Document(
            '',
            $artifact->getDocumentName(), // @phpstan-ignore-line
            $artifact->getDocumentType(), // @phpstan-ignore-line
            $artifact->getStorage(), // @phpstan-ignore-line
            $artifact->getDocumentPath()
        );

        return $this->storage->generateHttpStreamedResponse($document);
    }
}
