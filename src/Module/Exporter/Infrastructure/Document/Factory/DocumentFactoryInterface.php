<?php

namespace App\Module\Exporter\Infrastructure\Document\Factory;

use App\Module\Exporter\Infrastructure\Document\Model\DocumentInterface;
use App\Module\Exporter\Infrastructure\RequestExport\Message\Command\RequestExportCommandInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(DocumentFactoryResolver::FACTORY_TAG)]
interface DocumentFactoryInterface
{
    public function support(RequestExportCommandInterface $command): bool;

    public function createDocument(RequestExportCommandInterface $command): DocumentInterface;
}
