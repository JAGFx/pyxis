<?php

declare(strict_types=1);

namespace App\Infrastructure\Cqs\EventListener;

use App\Infrastructure\Cqs\Service\CorrelationIdService;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

readonly class ConsoleCorrelationIdListener
{
    public function __construct(
        private CorrelationIdService $correlationIdService,
    ) {
    }

    #[AsEventListener(event: ConsoleEvents::COMMAND, priority: 10)]
    public function onConsoleCommand(ConsoleCommandEvent $event): void
    {
        $correlationId = $this->correlationIdService->activateCorrelationId();

        $output = $event->getOutput();
        if ($output->isVerbose()) {
            $output->writeln(sprintf(
                '<comment>[Correlation ID: %s]</comment>',
                $correlationId
            ));
        }
    }
}
