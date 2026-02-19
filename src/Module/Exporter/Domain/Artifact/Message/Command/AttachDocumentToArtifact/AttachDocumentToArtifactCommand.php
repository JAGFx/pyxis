<?php

namespace App\Module\Exporter\Domain\Artifact\Message\Command\AttachDocumentToArtifact;

use App\Module\Exporter\Infrastructure\Document\Model\DocumentTypeEnum;
use App\Module\Exporter\Infrastructure\Storage\StorageEnum;
use App\Shared\Cqs\Message\Command\CommandInterface;

/**
 * @see AttachDocumentToArtifactHandler
 */
readonly class AttachDocumentToArtifactCommand implements CommandInterface
{
    public function __construct(
        private string $parentArtifactUuid,
        private string $requestExportCommandName,
        private string $documentName,
        private string $documentPath,
        private DocumentTypeEnum $documentType,
        private StorageEnum $storage,
        private bool $nested = false,
    ) {
    }

    public function getParentArtifactUuid(): ?string
    {
        return $this->parentArtifactUuid;
    }

    public function getRequestExportCommandName(): string
    {
        return $this->requestExportCommandName;
    }

    public function getDocumentName(): string
    {
        return $this->documentName;
    }

    public function getDocumentPath(): string
    {
        return $this->documentPath;
    }

    public function getDocumentType(): DocumentTypeEnum
    {
        return $this->documentType;
    }

    public function getStorage(): StorageEnum
    {
        return $this->storage;
    }

    public function isNested(): bool
    {
        return $this->nested;
    }
}
