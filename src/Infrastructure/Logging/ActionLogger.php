<?php

declare(strict_types=1);

namespace App\Infrastructure\Logging;

use App\Infrastructure\Cqs\Stamp\CorrelationStamp;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Throwable;

final readonly class ActionLogger
{
    public function __construct(
        private LoggerInterface $auditLogger,
        private LoggerInterface $performanceLogger,
    ) {
    }

    /**
     * @param mixed[] $context
     */
    public function logAction(string $action, array $context = []): void
    {
        $this->auditLogger->info($action, $context);
    }

    /**
     * @param mixed[] $context
     */
    public function logPerformance(string $action, float $duration, array $context = []): void
    {
        $this->performanceLogger->info($action, [
            'duration_ms' => round($duration * 1000, 2),
            ...$context,
        ]);
    }

    /**
     * @param mixed[] $context
     */
    public function logError(string $action, Throwable $error, array $context = []): void
    {
        $this->auditLogger->error($action, [
            'error' => $error->getMessage(),
            'file'  => $error->getFile(),
            'line'  => $error->getLine(),
            ...$context,
        ]);
    }

    public function extractCorrelationId(Envelope $envelope): ?string
    {
        $stamp = $envelope->last(CorrelationStamp::class);

        return $stamp?->correlationId;
    }
}
