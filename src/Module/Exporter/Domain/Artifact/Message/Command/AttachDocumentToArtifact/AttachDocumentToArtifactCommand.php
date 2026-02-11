<?php

namespace App\Module\Exporter\Domain\Artifact\Message\Command\AttachDocumentToArtifact;

use App\Module\Exporter\Infrastructure\Storage\StorageEnum;
use App\Shared\Cqs\Message\Command\CommandInterface;

/**
 * @see AttachDocumentToArtifactHandler
 */
readonly class AttachDocumentToArtifactCommand implements CommandInterface
{
    public function __construct(
        private string $artifactUuid,
        private string $documentName,
        private string $documentPath,
        private StorageEnum $storage,
        private bool $asLast = false,
        private ?string $parentArtifactUuid = null,
    ) {
    }

    public function hasParent(): bool
    {
        return !is_null($this->parentArtifactUuid);
    }

    public function getArtifactUuid(): string
    {
        return $this->artifactUuid;
    }

    public function getDocumentName(): string
    {
        return $this->documentName;
    }

    public function getDocumentPath(): string
    {
        return $this->documentPath;
    }

    public function getStorage(): StorageEnum
    {
        return $this->storage;
    }

    public function isAsLast(): bool
    {
        return $this->asLast;
    }

    public function getParentArtifactUuid(): ?string
    {
        return $this->parentArtifactUuid;
    }
}
