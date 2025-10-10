<?php

namespace App\Infrastructure\Cqs\Middleware;

use App\Infrastructure\Cqs\EventListener\CorrelationIdListener;
use App\Infrastructure\Cqs\Stamp\CorrelationStamp;
use OpenTelemetry\API\Baggage\Baggage;
use OpenTelemetry\API\Trace\Span;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\StampInterface;

#[AsTaggedItem('messenger.middleware', priority: 100)]
readonly class CorrelationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private RequestStack $requestStack,
    ) {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if ($envelope->last(CorrelationStamp::class) instanceof StampInterface) {
            return $stack->next()->handle($envelope, $stack);
        }

        $request = $this->requestStack->getCurrentRequest();

        /** @var ?string $correlationId */
        $correlationId = $request?->attributes->get(CorrelationIdListener::REQUEST_ATTRIBUTE_NAME);
        if (null !== $correlationId) {
            $envelope = $envelope->with(new CorrelationStamp($correlationId));
        }

        $span = Span::getCurrent();
        if ($span->isRecording()) {
            /** @var ?string $correlationId */
            $correlationId = Baggage::getCurrent()
                ->getEntry(CorrelationIdListener::OTEL_ATTRIBUTE_NAME)
                ?->getValue();

            if (null !== $correlationId) {
                $span->setAttribute(CorrelationIdListener::OTEL_ATTRIBUTE_NAME, $correlationId);
            }
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
