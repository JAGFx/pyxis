<?php

namespace App\Infrastructure\OpenTelemetry\Doctrine\Middleware;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsMiddleware;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Middleware;

#[AsMiddleware(connections: ['default'])]
final readonly class OpenTelemetryMiddleware implements Middleware
{
    public function wrap(Driver $driver): Driver
    {
        return new OpenTelemetryDriver($driver);
    }
}
