<?php

namespace App\Module\Exporter\Infrastructure\Document\Factory;

use App\Module\Exporter\Infrastructure\Document\Message\Query\ExporterQueryInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(DocumentFactoryResolver::FACTORY_TAG)]
interface DocumentFactoryInterface
{
    public function support(string $targetClass, DocumentTypeEnum $documentType): bool;

    public function createDocument(ExporterQueryInterface $query): DocumentInterface;
}
