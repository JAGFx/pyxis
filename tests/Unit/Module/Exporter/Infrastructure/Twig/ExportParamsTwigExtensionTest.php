<?php

declare(strict_types=1);

namespace App\Tests\Unit\Module\Exporter\Infrastructure\Twig;

use App\Module\Exporter\Infrastructure\Twig\ExportParamsTwigExtension;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Stringable;
use Symfony\Component\Form\FormView;

final class ExportParamsTwigExtensionTest extends TestCase
{
    private function generateExportParamsTwigExtension(): ExportParamsTwigExtension
    {
        return new ExportParamsTwigExtension();
    }

    private function generateFormView(array $fieldValues): FormView
    {
        $form = new FormView();

        foreach ($fieldValues as $name => $value) {
            $child                 = new FormView();
            $child->vars['value']  = $value;
            $form->children[$name] = $child;
        }

        return $form;
    }

    public static function singleFieldDataset(): Generator
    {
        $stringable = new class implements Stringable {
            public function __toString(): string
            {
                return 'bar';
            }
        };

        yield 'null is excluded' => [null, []];
        yield 'empty string is excluded' => ['', []];
        yield 'non-empty string is included' => ['foo', ['filters[field]' => 'foo']];
        yield 'true maps to 1' => [true, ['filters[field]' => '1']];
        yield 'false maps to 0' => [false, ['filters[field]' => '0']];
        yield 'integer is cast to string' => [42, ['filters[field]' => '42']];
        yield 'Stringable is cast to string' => [$stringable, ['filters[field]' => 'bar']];
    }

    #[DataProvider('singleFieldDataset')]
    public function testSingleField(mixed $value, array $expected): void
    {
        $form = $this->generateFormView(['field' => $value]);

        self::assertSame($expected, $this->generateExportParamsTwigExtension()->exportParams($form));
    }

    public function testMultipleFieldsMixedValues(): void
    {
        $form = $this->generateFormView(['a' => 'foo', 'b' => '', 'c' => true]);

        self::assertSame(
            ['filters[a]' => 'foo', 'filters[c]' => '1'],
            $this->generateExportParamsTwigExtension()->exportParams($form),
        );
    }
}
