<?php

namespace App\Module\Exporter\Infrastructure\Document\Factory;

use App\Module\Exporter\Infrastructure\Storage\StorageEnum;

interface DocumentInterface
{
    public function getPath(): string;

    public function getFileName(): string;

    public function getType(): DocumentTypeEnum;

    public function getStorage(): StorageEnum;
}
