<?php

namespace App\Module\Exporter\Domain\Account\Message\Query\ExportAccountData;

use App\Domain\Account\Entity\Account;
use App\Module\Exporter\Infrastructure\Document\Factory\DocumentTypeEnum;
use App\Module\Exporter\Infrastructure\Document\Message\Query\ExporterQueryInterface;
use App\Shared\Cqs\Message\Query\QueryInterface;

/**
 * @see ExportAccountDataHandler
 */
class ExportAccountDataQuery implements QueryInterface, ExporterQueryInterface
{
    public function __construct(
        private DocumentTypeEnum $documentType,
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

    public function setDocumentType(DocumentTypeEnum $documentType): ExportAccountDataQuery
    {
        $this->documentType = $documentType;

        return $this;
    }
}
