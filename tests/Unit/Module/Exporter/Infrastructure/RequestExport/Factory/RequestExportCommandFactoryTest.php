<?php

declare(strict_types=1);

namespace App\Tests\Unit\Module\Exporter\Infrastructure\RequestExport\Factory;

use App\Module\Exporter\Domain\Account\Message\Command\RequestExportAccountList\RequestExportAccountListCommand;
use App\Module\Exporter\Infrastructure\RequestExport\Factory\RequestExportCommandFactory;
use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RequestExportCommandFactoryTest extends TestCase
{
    private function generateRequestExportCommandFactory(array $map): RequestExportCommandFactory
    {
        return new RequestExportCommandFactory($map);
    }

    public function testCreateThrowsOnUnknownName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->generateRequestExportCommandFactory([])->create('unknown');
    }

    public static function createDataset(): Generator
    {
        yield 'account list' => [
            RequestExportAccountListCommand::NAME,
            RequestExportAccountListCommand::class,
        ];
    }

    #[DataProvider('createDataset')]
    public function testCreateReturnsCorrectInstance(string $name, string $expectedClass): void
    {
        $factory = $this->generateRequestExportCommandFactory([$name => $expectedClass]);

        self::assertInstanceOf($expectedClass, $factory->create($name));
    }
}
