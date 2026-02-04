<?php

namespace App\Module\Exporter\Infrastructure\Document\Factory;

use App\Module\Exporter\Infrastructure\Document\Message\Command\RequestExportCommandInterface;
use App\Module\Exporter\Infrastructure\Document\Model\DocumentInterface;
use App\Module\Exporter\Infrastructure\Document\Model\DocumentTypeEnum;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(DocumentFactoryResolver::FACTORY_TAG)]
interface DocumentFactoryInterface
{
    public function support(string $targetClass, DocumentTypeEnum $documentType): bool;

    public function createDocument(RequestExportCommandInterface $query): DocumentInterface;
}
