<?php

namespace App\Module\Exporter\Domain\Account\Factory;

use App\Domain\Account\Entity\Account;
use App\Domain\Account\Message\Query\FindAccounts\FindAccountsQuery;
use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Module\Exporter\Infrastructure\Document\Factory\DocumentFactoryInterface;
use App\Module\Exporter\Infrastructure\Document\Factory\DocumentInterface;
use App\Module\Exporter\Infrastructure\Document\Factory\DocumentTypeEnum;
use App\Module\Exporter\Infrastructure\Document\Factory\FileSystemDocument;
use App\Module\Exporter\Infrastructure\Document\Message\Query\ExporterQueryInterface;
use League\Csv\CannotInsertRecord;
use League\Csv\Exception;
use League\Csv\Writer;
use Override;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Throwable;

class CsvAccountDocumentFactory implements DocumentFactoryInterface
{
    private const string TEMP_DIR = '/exports';

    public function __construct(
        private readonly MessageBus $messageBus,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    #[Override]
    public function support(string $targetClass, DocumentTypeEnum $documentType): bool
    {
        return Account::class === $targetClass && DocumentTypeEnum::CSV === $documentType;
    }

    /**
     * @throws CannotInsertRecord
     * @throws Throwable
     * @throws Exception
     * @throws ExceptionInterface
     */
    #[Override]
    public function createDocument(ExporterQueryInterface $query): DocumentInterface
    {
        /** @var Account[] $accounts */
        $accounts = $this->messageBus->dispatch(new FindAccountsQuery());

        $header  = ['ID', 'Name'];
        $records = array_map(
            fn (Account $account): array => [
                $account->getId(),
                $account->getName(),
            ],
            $accounts
        );

        $csv = Writer::fromString();
        $csv->insertOne($header);
        $csv->insertAll($records);

        // ------- TODO: Temporary. To try it's working
        $content  = $csv->toString();
        $filename = sprintf('accounts_%s.csv', date('YmdHis'));

        $tempDir = $this->projectDir . '/private/' . self::TEMP_DIR;
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        // Sauvegarder le fichier
        $filePath = $tempDir . '/' . $filename;
        file_put_contents($filePath, $content);
        // -------

        return new FileSystemDocument(
            $filePath,
            $filename,
            DocumentTypeEnum::CSV
        );
    }
}
