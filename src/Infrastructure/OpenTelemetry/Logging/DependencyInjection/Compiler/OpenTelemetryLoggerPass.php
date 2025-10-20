<?php

namespace App\Infrastructure\OpenTelemetry\Logging\DependencyInjection\Compiler;

use App\Infrastructure\OpenTelemetry\Logging\Service\OpenTelemetryLogger;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class OpenTelemetryLoggerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $id => $definition) {
            // Filtrer uniquement les vrais loggers Monolog
            if (
                str_starts_with($id, 'monolog.logger')
                && !str_contains($id, '_prototype')  // ← Ignorer les prototypes
                && !str_contains($id, '.otel')       // ← Ignorer nos decorators
                && !$definition->isAbstract()        // ← Ignorer les définitions abstraites
            ) {
                $decoratorId = $id . '.otel';

                $decorator = new Definition(OpenTelemetryLogger::class);
                $decorator->setDecoratedService($id);
                $decorator->setAutowired(true);
                $decorator->setAutoconfigured(true);
                $decorator->setArgument('$logger', new Reference($decoratorId . '.inner'));

                $container->setDefinition($decoratorId, $decorator);
            }
        }
    }
}
