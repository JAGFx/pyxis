<?php

namespace App\Infrastructure\OpenTelemetry\EventListener;

use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\ScopeInterface;
use OpenTelemetry\SemConv\TraceAttributes;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class ConsoleListener
{
    private TracerInterface $tracer;
    private ?ScopeInterface $scope = null;
    private ?SpanInterface $span   = null;

    public function __construct()
    {
        $provider     = Globals::tracerProvider();
        $this->tracer = $provider->getTracer(
            'app.console',
            '1.0.0',
            TraceAttributes::SCHEMA_URL
        );
    }

    #[AsEventListener(event: ConsoleCommandEvent::class, priority: 1000)]
    public function startCommand(ConsoleCommandEvent $event): void
    {
        $command = $event->getCommand();

        if (!$command instanceof Command) {
            return;
        }

        /** @var non-empty-string $commandName */
        $commandName  = $command->getName() ?? 'unknown';
        $commandClass = $command::class;

        $input     = $event->getInput();
        $arguments = $input->getArguments();
        $options   = $input->getOptions();

        $this->span = $this->tracer
            ->spanBuilder($commandName)
            ->setSpanKind(SpanKind::KIND_INTERNAL)
            ->setAttribute(TraceAttributes::CODE_FUNCTION, 'execute')
            ->setAttribute(TraceAttributes::CODE_NAMESPACE, $commandClass)
            ->setAttribute('console.command.name', $commandName)
            ->startSpan();

        if ([] !== $arguments) {
            $this->span->setAttribute('console.command.arguments', json_encode($arguments));
        }

        $filteredOptions = array_filter($options, fn (array|bool|float|int|string|null $value): bool => null !== $value && false !== $value);
        if ([] !== $filteredOptions) {
            $this->span->setAttribute('console.command.options', json_encode($filteredOptions));
        }

        $this->scope = $this->span->activate();
    }

    #[AsEventListener(event: ConsoleErrorEvent::class, priority: -1000)]
    public function recordException(ConsoleErrorEvent $event): void
    {
        if (!$this->span instanceof SpanInterface) {
            return;
        }

        $error = $event->getError();

        $this->span->recordException($error, [
            'exception.escaped' => true,
        ]);

        $this->span->setStatus(StatusCode::STATUS_ERROR, $error->getMessage());
    }

    #[AsEventListener(event: ConsoleTerminateEvent::class, priority: -1000)]
    public function terminateCommand(ConsoleTerminateEvent $event): void
    {
        if (!$this->scope instanceof ScopeInterface || !$this->span instanceof SpanInterface) {
            return;
        }

        $exitCode = $event->getExitCode();

        $this->span->setAttribute('console.command.exit_code', $exitCode);

        match ($exitCode) {
            Command::SUCCESS => $this->span->setStatus(StatusCode::STATUS_OK, 'Command completed successfully'),
            Command::FAILURE => $this->span->setStatus(StatusCode::STATUS_ERROR, 'Command failed'),
            Command::INVALID => $this->span->setStatus(StatusCode::STATUS_ERROR, 'Invalid command'),
            default          => $this->span->setStatus(StatusCode::STATUS_ERROR, 'Command exited with code ' . $exitCode),
        };

        $detachResult = $this->scope->detach();

        if (0 !== $detachResult) {
            error_log('Warning: Scope detach returned non-zero value: ' . $detachResult);
        }

        $this->span->end();

        $this->scope = null;
        $this->span  = null;
    }
}
