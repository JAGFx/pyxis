<?php

namespace App\Module\Exporter\Infrastructure\Storage;

enum StorageEnum
{
    case FILE_SYSTEM;
    case S3;
}
