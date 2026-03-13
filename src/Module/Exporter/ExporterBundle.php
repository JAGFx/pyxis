<?php

namespace App\Module\Exporter;

use App\Module\Exporter\Infrastructure\RequestExport\Attribute\AsExportCommand;
use App\Module\Exporter\Infrastructure\RequestExport\DependencyInjection\RequestExportPass;
use App\Module\Exporter\Infrastructure\RequestExport\Factory\RequestExportCommandFactory;
use App\Module\Exporter\Infrastructure\RequestExport\Message\Command\RequestExportCommandInterface;
use Override;
use ReflectionClass;
use RuntimeException;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class ExporterBundle extends AbstractBundle
{
    #[Override]
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('enabled')
                    ->defaultTrue()
                    ->info('Enable or disable the Export module')
                ->end();
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RequestExportPass(), PassConfig::TYPE_BEFORE_REMOVING);
    }

    /**
     * @param array<string, mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->registerAttributeForAutoconfiguration(
            AsExportCommand::class,
            static function (
                ChildDefinition $definition,
                AsExportCommand $attribute,
                ReflectionClass $reflector,
            ): void {
                if (!$reflector->implementsInterface(RequestExportCommandInterface::class)) {
                    throw new RuntimeException(sprintf('Class "%s" is tagged with #[AsExportCommand] but does not implement "%s".', $reflector->getName(), RequestExportCommandInterface::class));
                }

                /** @var RequestExportCommandInterface $instance */
                $instance = $reflector->newInstance();

                $definition->addTag(RequestExportCommandFactory::DEFINITION_TAG, [
                    RequestExportCommandFactory::ATTRIBUTE_KEY_NAME => $instance->getName(),
                ]);
            }
        );
    }
}
