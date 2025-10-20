<?php

declare(strict_types=1);

namespace App\Infrastructure\Cqs\Middleware;

use App\Infrastructure\Cqs\Service\ActionLoggerService;
use App\Infrastructure\Cqs\Stamp\CorrelationStamp;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Throwable;

#[AsTaggedItem('messenger.middleware', priority: 110)]
final readonly class LoggingMiddleware implements MiddlewareInterface
{
    public function __construct(
        private ActionLoggerService $actionLogger,
    ) {
    }

    /**
     * @throws Throwable
     * @throws ExceptionInterface
     */
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message      = $envelope->getMessage();
        $messageClass = $message::class;
        $context      = $this->enrichContext($envelope);

        try {
            $this->actionLogger->logAction('Started: ' . $messageClass, $context);

            $envelope = $stack->next()->handle($envelope, $stack);

            $this->actionLogger->logAction('Completed: ' . $messageClass, $context);

            return $envelope;
        } catch (Throwable $throwable) {
            $this->actionLogger->logError(
                'Failed: ' . $messageClass,
                $throwable,
            );

            throw $throwable;
        }
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    private function enrichContext(Envelope $envelope, array $context = []): array
    {
        $message          = $envelope->getMessage();
        $correlationStamp = $envelope->last(CorrelationStamp::class);

        return [
            'correlation_id' => $correlationStamp?->correlationId,
            'message_class'  => $message::class,
            ...$context,
        ];
    }
}
