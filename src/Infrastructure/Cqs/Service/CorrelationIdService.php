<?php

declare(strict_types=1);

namespace App\Infrastructure\Cqs\Service;

use OpenTelemetry\API\Baggage\Baggage;
use OpenTelemetry\API\Trace\Span;
use Symfony\Component\Uid\Ulid;

readonly class CorrelationIdService
{
    public const string HEADER_NAME         = 'X-Correlation-ID';
    public const string OTEL_ATTRIBUTE_NAME = 'correlation.id';

    public function activateCorrelationId(?string $existingCorrelationId = null): string
    {
        $correlationId = $existingCorrelationId ?? new Ulid()->toBase32();

        $baggage = Baggage::getCurrent()
            ->toBuilder()
            ->set(self::OTEL_ATTRIBUTE_NAME, $correlationId)
            ->build();
        $baggage->activate();

        $span = Span::getCurrent();
        if ($span->isRecording()) {
            $span->setAttribute(self::OTEL_ATTRIBUTE_NAME, $correlationId);
        }

        return $correlationId;
    }
}
