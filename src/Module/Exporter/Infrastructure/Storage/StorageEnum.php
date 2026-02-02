<?php

namespace App\Module\Exporter\Infrastructure\Storage;

enum StorageEnum: string
{
    case FILE_SYSTEM = 'local';
    case S3          = 's3';
}
