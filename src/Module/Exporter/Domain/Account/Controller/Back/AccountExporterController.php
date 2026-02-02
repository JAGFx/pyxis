<?php

declare(strict_types=1);

namespace App\Module\Exporter\Domain\Account\Controller\Back;

use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Module\Exporter\Domain\Account\Message\Query\ExportListAccount\ExportListAccountQuery;
use App\Module\Exporter\Infrastructure\Document\Factory\DocumentInterface;
use App\Module\Exporter\Infrastructure\Document\Factory\DocumentTypeEnum;
use App\Module\Exporter\Infrastructure\Storage\StorageEnum;
use App\Module\Exporter\Infrastructure\Storage\StorageSystem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

#[Route('/exporters')]
class AccountExporterController extends AbstractController
{
    public function __construct(
        private readonly MessageBus $messageBus,
        private readonly StorageSystem $storageSystem,
    ) {
    }

    /**
     * @throws Throwable
     * @throws ExceptionInterface
     */
    #[Route(
        '/accounts/list',
        name: 'back_export_list_accounts',
        methods: Request::METHOD_GET
    )]
    public function exportList(): Response
    {
        $exportListAccountQuery = new ExportListAccountQuery(DocumentTypeEnum::CSV, StorageEnum::FILE_SYSTEM);

        /** @var DocumentInterface $document */
        $document = $this->messageBus->dispatch($exportListAccountQuery);

        return $this->storageSystem->getHttpStreamResponse($document);
    }
}
