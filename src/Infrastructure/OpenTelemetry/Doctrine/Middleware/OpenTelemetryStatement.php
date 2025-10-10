<?php

namespace App\Infrastructure\OpenTelemetry\Doctrine\Middleware;

use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\SemConv\TraceAttributes;
use Throwable;

final readonly class OpenTelemetryStatement implements Statement
{
    public function __construct(
        private Statement $innerStatement,
        private string $sql,
    ) {
    }

    public function bindValue(mixed $param, mixed $value, mixed $type = 2): bool
    {
        return $this->innerStatement->bindValue($param, $value, $type);
    }

    public function bindParam(mixed $param, mixed &$variable, mixed $type = 2, mixed $length = null): bool
    {
        return $this->innerStatement->bindParam($param, $variable, $type, $length);
    }

    public function execute(mixed $params = null): Result
    {
        $tracer = Globals::tracerProvider()->getTracer('doctrine');
        $span   = $tracer->spanBuilder('db.query')->setSpanKind(SpanKind::KIND_CLIENT)->startSpan();

        try {
            $span->setAttributes([
                TraceAttributes::DB_SYSTEM    => 'mysql',
                TraceAttributes::DB_STATEMENT => mb_substr($this->sql, 0, 1000),
            ]);

            $result = $this->innerStatement->execute($params);
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

    /**
     * @param array<mixed> $args
     */
    public function __call(string $method, array $args): mixed
    {
        return $this->innerStatement->$method(...$args);
    }
}
