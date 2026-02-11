<?php

namespace App\Module\Exporter\Infrastructure\RequestExport\Message\Command;

use App\Module\Exporter\Infrastructure\Document\Model\DocumentTypeEnum;
use App\Module\Exporter\Infrastructure\RequestExport\Model\RequestExportStageEnum;
use App\Module\Exporter\Infrastructure\Storage\StorageEnum;
use App\Shared\Cqs\Message\Command\CommandInterface;
use Symfony\Component\Uid\Uuid;

interface RequestExportCommandInterface extends CommandInterface
{
    public function getTarget(): string;

    public function getDocumentType(): DocumentTypeEnum;

    public function getStorage(): StorageEnum;

    public function getTranslationKey(): string;

    public function getStage(): RequestExportStageEnum;

    public function getArtifactUuid(): ?Uuid;

    public function setArtifactUuid(?Uuid $artifactUuid): self;
}
