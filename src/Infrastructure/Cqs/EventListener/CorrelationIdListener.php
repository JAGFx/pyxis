<?php

namespace App\Infrastructure\Cqs\EventListener;

use OpenTelemetry\API\Baggage\Baggage;
use OpenTelemetry\API\Trace\Span;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\Uid\Ulid;

class CorrelationIdListener
{
    public const string HEADER_NAME            = 'X-Correlation-ID';
    public const string REQUEST_ATTRIBUTE_NAME = '_correlation_id';
    public const string OTEL_ATTRIBUTE_NAME    = 'correlation.id';

    #[AsEventListener(event: RequestEvent::class, priority: 10)]
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request       = $event->getRequest();
        $correlationId = $request->headers->get(self::HEADER_NAME) ?? new Ulid()->toBase32();

        $baggage = Baggage::getCurrent()
            ->toBuilder()
            ->set(self::OTEL_ATTRIBUTE_NAME, $correlationId)
            ->build();
        $baggage->activate();

        $span = Span::getCurrent();
        if ($span->isRecording()) {
            $span->setAttribute(self::OTEL_ATTRIBUTE_NAME, $correlationId);
        }

        $request->attributes->set(self::REQUEST_ATTRIBUTE_NAME, $correlationId);
    }

    #[AsEventListener(event: ResponseEvent::class, priority: -10)]
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request  = $event->getRequest();
        $response = $event->getResponse();

        /** @var ?string $correlationId */
        $correlationId = $request->attributes->get(self::REQUEST_ATTRIBUTE_NAME);
        if (null !== $correlationId) {
            $response->headers->set(self::HEADER_NAME, $correlationId);
        }
    }
}
