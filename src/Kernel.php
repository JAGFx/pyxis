<?php

namespace App;

use App\Module\Exporter\Infrastructure\RequestExport\Attribute\AsExportCommand;
use App\Module\Exporter\Infrastructure\RequestExport\DependencyInjection\RequestExportPass;
use App\Module\Exporter\Infrastructure\RequestExport\Factory\RequestExportCommandFactory;
use App\Module\Exporter\Infrastructure\RequestExport\Message\Command\RequestExportCommandInterface;
use ReflectionClass;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function build(ContainerBuilder $container): void
    {
        // TODO: Export in separate class
        $container->registerAttributeForAutoconfiguration(
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

        $container->addCompilerPass(new RequestExportPass(), PassConfig::TYPE_BEFORE_REMOVING);
    }
}
