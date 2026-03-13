<?php

declare(strict_types=1);

namespace App\Tests\Unit\Module\Exporter\Domain\Account\Factory;

use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Module\Exporter\Domain\Account\Factory\CsvListAccountDocumentFactory;
use App\Module\Exporter\Domain\Account\Message\Command\RequestExportAccountList\RequestExportAccountListCommand;
use App\Module\Exporter\Infrastructure\Storage\StorageSystem;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CsvListAccountDocumentFactoryTest extends TestCase
{
    private function generateCsvListAccountDocumentFactory(): CsvListAccountDocumentFactory
    {
        return new CsvListAccountDocumentFactory(
            $this->createMock(MessageBus::class),
            $this->createMock(TranslatorInterface::class),
            $this->createMock(StorageSystem::class),
        );
    }

    private function generateCommand(array $filters): RequestExportAccountListCommand
    {
        return new RequestExportAccountListCommand()->setFilters($filters);
    }

    public static function enabledFilterDataset(): Generator
    {
        yield 'absent' => [[], null];
        yield 'empty string' => [['enabled' => ''], null];
        yield 'zero' => [['enabled' => '0'], false];
        yield 'one' => [['enabled' => '1'], true];
    }

    #[DataProvider('enabledFilterDataset')]
    public function testEnabledFilter(array $filters, ?bool $expected): void
    {
        $query = $this->generateCsvListAccountDocumentFactory()->buildQuery($this->generateCommand($filters));

        self::assertSame($expected, $query->isEnabled());
    }

    public static function nameFilterDataset(): Generator
    {
        yield 'absent' => [[], null];
        yield 'empty string' => [['name' => ''], null];
        yield 'non-empty' => [['name' => 'Épargne'], 'Épargne'];
    }

    #[DataProvider('nameFilterDataset')]
    public function testNameFilter(array $filters, ?string $expected): void
    {
        $query = $this->generateCsvListAccountDocumentFactory()->buildQuery($this->generateCommand($filters));

        self::assertSame($expected, $query->getName());
    }
}
