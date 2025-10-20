<?php

namespace App\Infrastructure\OpenTelemetry\Logging\EventListener;

use App\Infrastructure\OpenTelemetry\Logging\Factory\LoggerProviderFactory;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::TERMINATE, priority: -1024)]
final readonly class FlushLoggerListener
{
    public function __construct(
        private LoggerProviderFactory $factory,
    ) {
    }

    public function __invoke(): void
    {
        // Flush après l'envoi de la réponse HTTP
        $provider = $this->factory->create();
        $provider->forceFlush();
    }
}
