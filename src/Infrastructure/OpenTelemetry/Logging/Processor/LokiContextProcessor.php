<?php

namespace App\Infrastructure\OpenTelemetry\Logging\Processor;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use OpenTelemetry\API\Trace\Span;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

#[AutoconfigureTag('monolog.processor', ['priority' => 120])]
readonly class LokiContextProcessor implements ProcessorInterface
{
    public function __construct(
        private RequestStack $requestStack,
    ) {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        // === Trace Context ===
        $span = Span::getCurrent();
        $span->getContext();

        // === Request Context (si disponible) ===
        $request = $this->requestStack->getCurrentRequest();
        if ($request instanceof Request) {
            $record->extra['http_method'] = $request->getMethod();
            $record->extra['http_path']   = $request->getPathInfo();
            $record->extra['http_route']  = $request->attributes->get('_route');
        }

        // === Metadata ===
        $record->extra['log_channel']  = $record->channel;
        $record->extra['timestamp_ms'] = (int) $record->datetime->format('Uv');
        $record->extra['hostname']     = gethostname();

        return $record;
    }
}
