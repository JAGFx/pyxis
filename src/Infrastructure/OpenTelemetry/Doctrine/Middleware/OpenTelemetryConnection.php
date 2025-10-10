<?php

namespace App\Infrastructure\OpenTelemetry\Doctrine\Middleware;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\SemConv\TraceAttributes;
use Throwable;

final readonly class OpenTelemetryConnection implements Connection
{
    public function __construct(private Connection $innerConnection)
    {
    }

    public function prepare(string $sql): Statement
    {
        return new OpenTelemetryStatement($this->innerConnection->prepare($sql), $sql);
    }

    /**
     * @throws Throwable
     */
    public function query(string $sql): Result
    {
        /* @var Result */
        return $this->trace($sql, 'db.query', fn (): Result => $this->innerConnection->query($sql));
    }

    /**
     * @throws Throwable
     */
    public function exec(string $sql): int
    {
        /* @var int */
        return $this->trace($sql, 'db.exec', fn (): int => $this->innerConnection->exec($sql));
    }

    public function quote(mixed $value, mixed $type = 2): string
    {
        /** @var string */
        $result = $this->innerConnection->quote($value, $type);

        return $result;
    }

    public function lastInsertId(mixed $name = null): int|string
    {
        $result = $this->innerConnection->lastInsertId($name);

        return false === $result ? 0 : $result;
    }

    public function beginTransaction(): bool
    {
        return $this->innerConnection->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->innerConnection->commit();
    }

    public function rollBack(): bool
    {
        return $this->innerConnection->rollBack();
    }

    public function getNativeConnection(): mixed
    {
        return $this->innerConnection->getNativeConnection();
    }

    /**
     * @template T
     *
     * @param non-empty-string $spanName
     * @param callable(): T    $operation
     *
     * @return T
     */
    private function trace(string $sql, string $spanName, callable $operation): mixed
    {
        $tracer = Globals::tracerProvider()->getTracer('doctrine');
        $span   = $tracer->spanBuilder($spanName)->setSpanKind(SpanKind::KIND_CLIENT)->startSpan();

        try {
            $span->setAttributes([
                TraceAttributes::DB_SYSTEM    => 'mysql',
                TraceAttributes::DB_STATEMENT => mb_substr($sql, 0, 1000),
            ]);

            $result = $operation();
            $span->setStatus(StatusCode::STATUS_OK);

            return $result;
        } catch (Throwable $throwable) {
            $span->recordException($throwable);
            $span->setStatus(StatusCode::STATUS_ERROR);
            throw $throwable;
        } finally {
            $span->end();
        }
    }
}
