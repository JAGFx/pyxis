<?php

declare(strict_types=1);

namespace App\Module\Exporter\Infrastructure\RequestExport\DependencyInjection;

use App\Module\Exporter\Infrastructure\RequestExport\Factory\RequestExportCommandFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RequestExportPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $map = [];

        /** @var array<string, array<int, array<string, string>>> $ressourcesAttributes */
        $ressourcesAttributes = $container->findTaggedResourceIds(RequestExportCommandFactory::DEFINITION_TAG);

        foreach ($ressourcesAttributes as $class => $occurrences) {
            $name = $occurrences[0][RequestExportCommandFactory::ATTRIBUTE_KEY_NAME] ?? null;

            if (is_null($name)) {
                continue;
            }

            $map[$name] = $class;
        }

        $container
            ->getDefinition(RequestExportCommandFactory::class)
            ->setArgument('requestExportCommandMap', $map);
    }
}
