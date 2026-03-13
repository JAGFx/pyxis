<?php

declare(strict_types=1);

namespace App\Module\Exporter\Infrastructure\RequestExport\Message\Command;

use App\Module\Exporter\Infrastructure\Document\Model\DocumentTypeEnum;
use App\Module\Exporter\Infrastructure\RequestExport\Model\RequestExportStageEnum;
use App\Module\Exporter\Infrastructure\Storage\StorageEnum;
use Symfony\Component\Uid\Uuid;

abstract class AbstractRequestExportCommand implements RequestExportCommandInterface
{
    public const string FILTERS_KEY = 'filters';

    protected RequestExportStageEnum $stage = RequestExportStageEnum::ARTIFACT_CREATION;

    protected Uuid $parentArtifactUuid;

    /** @var array<string, string> */
    protected array $filters = [];

    public function __construct(
        protected DocumentTypeEnum $documentType = DocumentTypeEnum::CSV,
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

    public function getParentArtifactUuid(): Uuid
    {
        return $this->parentArtifactUuid;
    }

    public function setParentArtifactUuid(Uuid $artifactUuid): self
    {
        $this->parentArtifactUuid = $artifactUuid;
        $this->stage              = RequestExportStageEnum::EXPORTING;

        return $this;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    /** @param array<string, string> $filters */
    public function setFilters(array $filters): static
    {
        $this->filters = $filters;

        return $this;
    }
}
