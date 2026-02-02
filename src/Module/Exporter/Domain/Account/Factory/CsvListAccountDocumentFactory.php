<?php

namespace App\Module\Exporter\Domain\Account\Factory;

use App\Domain\Account\Entity\Account;
use App\Domain\Account\Message\Query\FindAccounts\FindAccountsQuery;
use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Module\Exporter\Infrastructure\Document\Factory\DocumentFactoryInterface;
use App\Module\Exporter\Infrastructure\Document\Factory\DocumentInterface;
use App\Module\Exporter\Infrastructure\Document\Factory\DocumentTypeEnum;
use App\Module\Exporter\Infrastructure\Document\Message\Query\ExporterQueryInterface;
use App\Module\Exporter\Infrastructure\Document\Model\Document;
use App\Module\Exporter\Infrastructure\Storage\StorageSystem;
use League\Csv\CannotInsertRecord;
use League\Csv\Exception;
use League\Csv\Writer;
use Override;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

readonly class CsvListAccountDocumentFactory implements DocumentFactoryInterface
{
    public function __construct(
        private MessageBus $messageBus,
        private TranslatorInterface $translator,
        private StorageSystem $storageSystem,
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
        [$header, $records] = $this->getRawData();

        $csv = Writer::fromString();
        $csv->insertOne($header);
        $csv->insertAll($records);

        $content  = $csv->toString();
        $filename = sprintf('accounts_%s.csv', date('YmdHis'));
        $path     = 'account/list/' . $filename;

        $document = new Document(
            $path,
            $filename,
            DocumentTypeEnum::CSV,
            $query->getStorage()
        );

        $this->storageSystem->write(
            $document,
            $content
        );

        return $document;
    }

    /**
     * @return array{string[], array<array{int|null, string}>}
     *
     * @throws Throwable
     * @throws ExceptionInterface
     */
    public function getRawData(): array
    {
        /** @var Account[] $accounts */
        $accounts = $this->messageBus->dispatch(new FindAccountsQuery());

        $header = [
            $this->translator->trans('exporters.account.list.csv.headers.id', domain: 'document'),
            $this->translator->trans('exporters.account.list.csv.headers.name', domain: 'document'),
        ];
        $records = array_map(
            fn (Account $account): array => [
                $account->getId(),
                $account->getName(),
            ],
            $accounts
        );

        return [$header, $records];
    }
}
