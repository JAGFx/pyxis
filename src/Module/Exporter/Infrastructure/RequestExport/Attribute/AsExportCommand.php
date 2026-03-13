<?php

namespace App\Module\Exporter\Infrastructure\RequestExport\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class AsExportCommand
{
}
