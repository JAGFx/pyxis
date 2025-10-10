<?php

namespace App\Infrastructure\OpenTelemetry\Doctrine\Middleware;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\API\ExceptionConverter;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;

final readonly class OpenTelemetryDriver implements Driver
{
    public function __construct(private Driver $innerDriver)
    {
    }

    public function connect(array $params): Connection
    {
        $connection = $this->innerDriver->connect($params);

        return new OpenTelemetryConnection($connection);
    }

    public function getDatabasePlatform(): AbstractPlatform
    {
        return $this->innerDriver->getDatabasePlatform();
    }

    /**
     * @return AbstractSchemaManager<AbstractPlatform>
     */
    public function getSchemaManager(\Doctrine\DBAL\Connection $conn, AbstractPlatform $platform): AbstractSchemaManager
    {
        return $this->innerDriver->getSchemaManager($conn, $platform);
    }

    public function getExceptionConverter(): ExceptionConverter
    {
        return $this->innerDriver->getExceptionConverter();
    }
}
