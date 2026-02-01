<?php

namespace App\Infrastructure\Module\Trait;

use Symfony\Component\DependencyInjection\ContainerBuilder;

trait TranslatableModuleTrait
{
    private function useTranslation(string $basePath, ContainerBuilder $builder): void
    {
        $translationPaths = [];
        $modulesDir       = realpath($basePath);
        $translationDirs  = glob($modulesDir . '/translations');

        if (false === $translationDirs) {
            return;
        }

        foreach ($translationDirs as $translationDir) {
            if (!is_dir($translationDir)) {
                continue;
            }

            $translationPaths[] = $translationDir;
        }

        $builder->prependExtensionConfig('framework', [
            'translator' => [
                'paths' => $translationPaths,
            ],
        ]);
    }
}
