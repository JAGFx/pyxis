<?php

namespace App\Module\Exporter\Infrastructure\Document\Factory;

class FileSystemDocument implements DocumentInterface
{
    public function __construct(
        private readonly string $path,
        private readonly string $fileName,
        private readonly DocumentTypeEnum $type,
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
}
