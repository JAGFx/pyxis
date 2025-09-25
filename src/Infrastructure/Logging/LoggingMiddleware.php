<?php

declare(strict_types=1);

namespace App\Infrastructure\Logging;

use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Throwable;

#[AsTaggedItem('messenger.middleware', priority: 90)]
final readonly class LoggingMiddleware implements MiddlewareInterface
{
    public function __construct(
        private ActionLogger $actionLogger,
    ) {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message       = $envelope->getMessage();
        $messageClass  = $message::class;
        $correlationId = $this->actionLogger->extractCorrelationId($envelope);

        $startTime = microtime(true);

        try {
            $this->actionLogger->logAction('Started: ' . $messageClass, [
                'correlation_id' => $correlationId,
                'message_class'  => $messageClass,
            ]);

            $envelope = $stack->next()->handle($envelope, $stack);

            $duration = microtime(true) - $startTime;

            $this->actionLogger->logAction('Completed: ' . $messageClass, [
                'correlation_id' => $correlationId,
                'message_class'  => $messageClass,
            ]);

            $this->actionLogger->logPerformance($messageClass, $duration, [
                'correlation_id' => $correlationId,
                'message_class'  => $messageClass,
            ]);

            return $envelope;
        } catch (Throwable $throwable) {
            $this->actionLogger->logError('Failed: ' . $messageClass, $throwable, [
                'correlation_id' => $correlationId,
                'message_class'  => $messageClass,
                'duration_ms'    => round((microtime(true) - $startTime) * 1000, 2),
            ]);

            throw $throwable;
        }
    }
}
