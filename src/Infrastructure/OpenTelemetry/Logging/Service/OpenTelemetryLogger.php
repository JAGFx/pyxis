<?php

namespace App\Infrastructure\OpenTelemetry\Logging\Service;

use App\Infrastructure\OpenTelemetry\Logging\Handler\OpenTelemetryHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Stringable;

final class OpenTelemetryLogger implements LoggerInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private OpenTelemetryHandler $otelHandler,
    ) {
        // Ajouter le handler au logger Monolog
        if ($this->logger instanceof Logger) {
            // Vérifier qu'il n'est pas déjà présent
            $handlers = $this->logger->getHandlers();

            foreach ($handlers as $handler) {
                if ($handler === $this->otelHandler) {
                    return;
                }
            }

            $this->logger->pushHandler($this->otelHandler);
        }
    }

    public function emergency(Stringable|string $message, array $context = []): void
    {
        $this->logger->emergency($message, $context);
    }

    public function alert(Stringable|string $message, array $context = []): void
    {
        $this->logger->alert($message, $context);
    }

    public function critical(Stringable|string $message, array $context = []): void
    {
        $this->logger->critical($message, $context);
    }

    public function error(Stringable|string $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    public function warning(Stringable|string $message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    public function notice(Stringable|string $message, array $context = []): void
    {
        $this->logger->notice($message, $context);
    }

    public function info(Stringable|string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    public function debug(Stringable|string $message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }

    public function log($level, Stringable|string $message, array $context = []): void
    {
        $this->logger->log($level, $message, $context);
    }
}
