<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Logging;

use App\Domain\PeriodicEntry\Exception\PeriodicEntrySplitBudgetException;
use App\Infrastructure\Logging\ActionLogger;
use App\Infrastructure\Logging\LoggingMiddleware;
use Generator;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Middleware\StackMiddleware;
use Throwable;

class LoggingMiddlewareTest extends TestCase
{
    public static function failuresDataset(): Generator
    {
        $envelope = new Envelope(new stdClass());
        $skip     = new PeriodicEntrySplitBudgetException('The periodic entry is not scheduled for today.');

        yield 'Expected failure' => [$skip, 'info', 'Skipped: stdClass', 'The periodic entry is not scheduled for today.'];
        yield 'Expected failure wrapped by Messenger' => [new HandlerFailedException($envelope, [$skip]), 'info', 'Skipped: stdClass', 'The periodic entry is not scheduled for today.'];
        yield 'Unexpected failure' => [new LogicException('Boom'), 'error', 'Failed: stdClass', null];
        yield 'Unexpected failure wrapped by Messenger' => [new HandlerFailedException($envelope, [new LogicException('Boom')]), 'error', 'Failed: stdClass', null];
    }

    #[DataProvider('failuresDataset')]
    public function testFailureLevel(Throwable $failure, string $expectedLevel, string $expectedMessage, ?string $expectedReason): void
    {
        $logs   = [];
        $record = static function (string $level) use (&$logs): callable {
            return static function (string $message, array $context = []) use ($level, &$logs): void {
                $logs[] = [$level, $message, $context['reason'] ?? null];
            };
        };

        $auditLogger = $this->createStub(LoggerInterface::class);
        $auditLogger->method('info')->willReturnCallback($record('info'));
        $auditLogger->method('error')->willReturnCallback($record('error'));

        $middleware = new LoggingMiddleware(new ActionLogger($auditLogger, $this->createStub(LoggerInterface::class)));

        try {
            $middleware->handle(new Envelope(new stdClass()), $this->throwingStack($failure));
            self::fail('The failure must be rethrown');
        } catch (Throwable $thrown) {
            self::assertSame($failure, $thrown);
        }

        self::assertSame([
            ['info', 'Started: stdClass', null],
            [$expectedLevel, $expectedMessage, $expectedReason],
        ], $logs);
    }

    private function throwingStack(Throwable $failure): StackInterface
    {
        return new StackMiddleware(new class($failure) implements MiddlewareInterface {
            public function __construct(
                private readonly Throwable $failure,
            ) {
            }

            public function handle(Envelope $envelope, StackInterface $stack): Envelope
            {
                throw $this->failure;
            }
        });
    }
}
