<?php

namespace App\Infrastructure\Cqs\Middleware;

use App\Infrastructure\Cqs\EventListener\CorrelationIdListener;
use App\Infrastructure\Cqs\Stamp\CorrelationStamp;
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

        return $stack->next()->handle($envelope, $stack);
    }
}
