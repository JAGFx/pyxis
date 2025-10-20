<?php

namespace App\Infrastructure\OpenTelemetry\Logging\Handler;

use App\Infrastructure\OpenTelemetry\Logging\Factory\LoggerProviderFactory;
use OpenTelemetry\Contrib\Logs\Monolog\Handler;
use Psr\Log\LogLevel;

class OpenTelemetryHandler extends Handler
{
    public function __construct(
        LoggerProviderFactory $factory,
    ) {
        $provider = $factory->create();
        parent::__construct($provider, LogLevel::DEBUG);
    }
}
