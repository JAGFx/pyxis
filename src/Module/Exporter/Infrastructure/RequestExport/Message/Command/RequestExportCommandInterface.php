<?php

namespace App\Module\Exporter\Infrastructure\RequestExport\Message\Command;

use App\Module\Exporter\Infrastructure\Document\Model\DocumentTypeEnum;
use App\Module\Exporter\Infrastructure\RequestExport\Model\RequestExportStageEnum;
use App\Module\Exporter\Infrastructure\Storage\StorageEnum;
use App\Shared\Cqs\Message\Command\CommandInterface;
use Symfony\Component\Uid\Uuid;

interface RequestExportCommandInterface extends CommandInterface
{
    public function getName(): string;

    public function getDocumentType(): DocumentTypeEnum;

    public function getStorage(): StorageEnum;

    public function getStage(): RequestExportStageEnum;

    public function getParentArtifactUuid(): Uuid;

    public function setParentArtifactUuid(Uuid $artifactUuid): self;

    public function setDocumentType(DocumentTypeEnum $documentType): self;

    /** @return array<string, string> */
    public function getFilters(): array;

    /** @param array<string, string> $filters */
    public function setFilters(array $filters): static;
}
