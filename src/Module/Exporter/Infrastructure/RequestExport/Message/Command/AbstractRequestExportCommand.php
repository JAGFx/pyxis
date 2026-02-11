<?php

namespace App\Module\Exporter\Infrastructure\RequestExport\Message\Command;

use App\Module\Exporter\Infrastructure\Document\Model\DocumentTypeEnum;
use App\Module\Exporter\Infrastructure\RequestExport\Model\RequestExportStageEnum;
use App\Module\Exporter\Infrastructure\Storage\StorageEnum;
use Symfony\Component\Uid\Uuid;

abstract class AbstractRequestExportCommand implements RequestExportCommandInterface
{
    protected RequestExportStageEnum $stage = RequestExportStageEnum::ARTIFACT_CREATION;

    protected ?Uuid $artifactUuid = null;

    public function __construct(
        protected DocumentTypeEnum $documentType,
        protected StorageEnum $storage = StorageEnum::S3,
    ) {
    }

    public function isOnExportingStage(): bool
    {
        return RequestExportStageEnum::EXPORTING === $this->stage;
    }

    public function getDocumentType(): DocumentTypeEnum
    {
        return $this->documentType;
    }

    public function setDocumentType(DocumentTypeEnum $documentType): self
    {
        $this->documentType = $documentType;

        return $this;
    }

    public function getStorage(): StorageEnum
    {
        return $this->storage;
    }

    public function setStorage(StorageEnum $storage): self
    {
        $this->storage = $storage;

        return $this;
    }

    public function getStage(): RequestExportStageEnum
    {
        return $this->stage;
    }

    public function getArtifactUuid(): ?Uuid
    {
        return $this->artifactUuid;
    }

    public function setArtifactUuid(?Uuid $artifactUuid): self
    {
        $this->artifactUuid = $artifactUuid;
        $this->stage        = RequestExportStageEnum::EXPORTING;

        return $this;
    }
}
