<?php

declare(strict_types=1);

namespace App\Module\Exporter\Domain\Artifact\Controller\Back;

use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Infrastructure\KnpPaginator\Controller\PaginationFormHandlerTrait;
use App\Infrastructure\KnpPaginator\DTO\OrderEnum;
use App\Module\Exporter\Domain\Artifact\Form\ArtifactSearchType;
use App\Module\Exporter\Domain\Artifact\Message\Query\FindArtifacts\FindArtifactsQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

#[Route('/exporter')]
class ArtifactController extends AbstractController
{
    use PaginationFormHandlerTrait;

    public function __construct(
        private readonly MessageBus $messageBus,
    ) {
    }

    /**
     * @throws Throwable
     * @throws ExceptionInterface
     */
    #[Route(
        '/requests',
        name: 'back_exporter_list_artifacts',
        methods: Request::METHOD_GET
    )]
    public function requestList(Request $request): Response
    {
        $searchQuery = new FindArtifactsQuery()
            ->setOrderBy('createdAt')
            ->setOrderDirection(OrderEnum::DESC);

        $this->handlePaginationForm($request, ArtifactSearchType::class, $searchQuery);

        return $this->render('module/exporter/domain/artifact/index.html.twig', [
            'artifacts' => $this->messageBus->dispatch($searchQuery),
        ]);
    }
}
