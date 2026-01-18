<?php

namespace App\Module\Exporter\Infrastructure\Document\Factory;

interface DocumentInterface
{
    public function getPath(): string;

    public function getFileName(): string;

    public function getType(): DocumentTypeEnum;
}
