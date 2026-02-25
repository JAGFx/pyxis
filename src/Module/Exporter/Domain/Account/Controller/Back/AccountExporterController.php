<?php

declare(strict_types=1);

namespace App\Module\Exporter\Domain\Account\Controller\Back;

use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Module\Exporter\Domain\Account\Message\Command\RequestExportAccountList\RequestExportAccountListCommand;
use App\Module\Exporter\Infrastructure\Document\Model\DocumentTypeEnum;
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
        '/accounts/list',
        name: 'back_exporter_list_accounts',
        methods: Request::METHOD_GET
    )]
    public function exportList(): Response
    {
        $requestExportAccountListCommand = new RequestExportAccountListCommand(DocumentTypeEnum::CSV);

        $this->messageBus->dispatch($requestExportAccountListCommand);

        return $this->redirectToRoute('back_exporter_list_artifacts');
    }
}
