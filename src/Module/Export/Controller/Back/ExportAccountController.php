<?php

declare(strict_types=1);

namespace App\Module\Export\Controller\Back;

use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Module\Export\Domain\Message\Query\ExportAccountData\ExportAccountDataQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

#[Route('/exports')]
class ExportAccountController extends AbstractController
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
        $this->messageBus->dispatch(new ExportAccountDataQuery());

        return $this->redirectToRoute('home');
    }
}
