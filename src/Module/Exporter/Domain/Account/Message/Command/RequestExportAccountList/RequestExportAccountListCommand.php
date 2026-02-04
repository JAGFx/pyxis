<?php

namespace App\Module\Exporter\Domain\Account\Message\Command\RequestExportAccountList;

use App\Domain\Account\Entity\Account;
use App\Module\Exporter\Infrastructure\Document\Message\Command\RequestExportCommandInterface;
use App\Module\Exporter\Infrastructure\Document\Model\DocumentTypeEnum;
use App\Module\Exporter\Infrastructure\Storage\StorageEnum;
use App\Shared\Cqs\Message\Command\CommandInterface;

/**
 * @see RequestExportAccountListHandler
 */
class RequestExportAccountListCommand implements CommandInterface, RequestExportCommandInterface
{
    public function __construct(
        private DocumentTypeEnum $documentType,
        private StorageEnum $storage = StorageEnum::S3,
    ) {
    }

    public function getTarget(): string
    {
        return Account::class;
    }

    public function getDocumentType(): DocumentTypeEnum
    {
        return $this->documentType;
    }

    public function setDocumentType(DocumentTypeEnum $documentType): RequestExportAccountListCommand
    {
        $this->documentType = $documentType;

        return $this;
    }

    public function getStorage(): StorageEnum
    {
        return $this->storage;
    }

    public function setStorage(StorageEnum $storage): RequestExportAccountListCommand
    {
        $this->storage = $storage;

        return $this;
    }
}
