<?php

namespace App\Infrastructure\Cqs\Middleware;

use OpenTelemetry\API\Trace\Span;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

#[AsTaggedItem('messenger.middleware', priority: 100)]
class OpenTelemetryMessagingAttributesMiddleware implements MiddlewareInterface
{
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $span = Span::getCurrent();

        if ($span->isRecording()) {
            $message      = $envelope->getMessage();
            $messageClass = get_class($message);

            $messageInterfaces = class_implements($message);

            $span->setAttribute('cqs.message.type', implode(',', $messageInterfaces));
            $span->setAttribute('cqs.message.name', $messageClass);
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
