<?php

declare(strict_types=1);

namespace App\Module\Exporter\Domain\Account\Controller\Back;

use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Module\Exporter\Domain\Account\Message\Query\ExportAccountData\ExportAccountDataQuery;
use App\Module\Exporter\Infrastructure\Document\Factory\DocumentInterface;
use App\Module\Exporter\Infrastructure\Document\Factory\DocumentTypeEnum;
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
    ) {
    }

    /**
     * @throws Throwable
     * @throws ExceptionInterface
     */
    #[Route(
        '/accounts',
        name: 'back_export_accounts',
        methods: Request::METHOD_GET
    )]
    public function index(): Response
    {
        /** @var DocumentInterface $document */
        $document = $this->messageBus->dispatch(new ExportAccountDataQuery(DocumentTypeEnum::CSV));

        return $this->file(
            $document->getPath(),
            $document->getFileName()
        );
    }
}
