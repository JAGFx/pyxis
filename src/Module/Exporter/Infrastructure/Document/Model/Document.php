<?php

namespace App\Module\Exporter\Infrastructure\Document\Model;

use App\Module\Exporter\Infrastructure\Storage\StorageEnum;

readonly class Document implements DocumentInterface
{
    public function __construct(
        private string $path,
        private string $fileName,
        private DocumentTypeEnum $type,
        private StorageEnum $storage,
        private ?string $absolutePath = null,
    ) {
    }

    public function getPath(): string
    {
        if (!is_null($this->absolutePath)) {
            return $this->absolutePath;
        }

        /* @see https://flysystem.thephpleague.com/docs/advanced/mount-manager/ */
        return $this->storage->value . '://' . $this->path;
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
