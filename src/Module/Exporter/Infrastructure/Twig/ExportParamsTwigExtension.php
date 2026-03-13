<?php

declare(strict_types=1);

namespace App\Module\Exporter\Infrastructure\Twig;

use App\Module\Exporter\Infrastructure\RequestExport\Message\Command\AbstractRequestExportCommand;
use Stringable;
use Symfony\Component\Form\FormView;
use Twig\Attribute\AsTwigFunction;

class ExportParamsTwigExtension
{
    /**
     * Reads non-empty form field values and maps them to `filters[name]` URL params.
     *
     * @return array<string, string>
     */
    #[AsTwigFunction('export_params')]
    public function exportParams(FormView $form): array
    {
        $result = [];
        foreach ($form->children as $name => $child) {
            $value = $child->vars['value'] ?? null;

            $stringValue = match (true) {
                null === $value              => null,
                is_bool($value)              => $value ? '1' : '0',
                is_scalar($value)            => (string) $value,
                $value instanceof Stringable => (string) $value,
                default                      => null,
            };

            if (null !== $stringValue && '' !== $stringValue) {
                $result[AbstractRequestExportCommand::FILTERS_KEY . '[' . $name . ']'] = $stringValue;
            }
        }

        return $result;
    }
}
