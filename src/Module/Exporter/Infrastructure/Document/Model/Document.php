<?php

namespace App\Module\Exporter\Infrastructure\Document\Model;

use App\Module\Exporter\Infrastructure\Document\Factory\DocumentInterface;
use App\Module\Exporter\Infrastructure\Document\Factory\DocumentTypeEnum;
use App\Module\Exporter\Infrastructure\Storage\StorageEnum;

readonly class Document implements DocumentInterface
{
    public function __construct(
        private string $path,
        private string $fileName,
        private DocumentTypeEnum $type,
        private StorageEnum $storage,
    ) {
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getType(): DocumentTypeEnum
    {
        return $this->type;
    }

    public function getStorage(): StorageEnum
    {
        return $this->storage;
    }
}
