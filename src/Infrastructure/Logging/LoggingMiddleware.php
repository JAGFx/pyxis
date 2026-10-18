<?php

declare(strict_types=1);

namespace App\Infrastructure\Logging;

use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
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
            $context = [
                'correlation_id' => $correlationId,
                'message_class'  => $messageClass,
                'duration_ms'    => round((microtime(true) - $startTime) * 1000, 2),
            ];

            $expectedFailure = $this->findExpectedFailure($throwable);

            if ($expectedFailure instanceof Throwable) {
                $this->actionLogger->logSkipped('Skipped: ' . $messageClass, $expectedFailure, $context);
            } else {
                $this->actionLogger->logError('Failed: ' . $messageClass, $throwable, $context);
            }

            throw $throwable;
        }
    }

    /**
     * Messenger wraps the handler exceptions in a HandlerFailedException: the original exception is returned
     * when every wrapped exception is an expected failure.
     */
    private function findExpectedFailure(Throwable $throwable): ?Throwable
    {
        $exceptions = $throwable instanceof HandlerFailedException
            ? array_values($throwable->getWrappedExceptions())
            : [$throwable];

        $allExpected = [] !== $exceptions
            && array_all($exceptions, static fn (Throwable $exception): bool => $exception instanceof ExpectedFailureInterface);

        return $allExpected ? $exceptions[0] : null;
    }
}
