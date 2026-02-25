<?php

namespace App\Module\Exporter\Domain\Artifact\Controller\Front;

use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Infrastructure\KnpPaginator\DTO\OrderEnum;
use App\Infrastructure\Turbo\Controller\TurboResponseTrait;
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
    use TurboResponseTrait;

    public function __construct(
        private readonly MessageBus $messageBus,
    ) {
    }

    /**
     * @throws Throwable
     * @throws ExceptionInterface
     */
    #[Route(
        '/requests/search',
        name: 'front_exporter_search_artifacts',
        methods: [Request::METHOD_POST]
    )]
    public function search(Request $request): Response
    {
        $searchQuery = new FindArtifactsQuery()
            ->setOrderBy('createdAt')
            ->setOrderDirection(OrderEnum::DESC);

        $this->createForm(ArtifactSearchType::class, $searchQuery)
            ->handleRequest($request);

        $artifacts = $this->messageBus->dispatch($searchQuery);

        return $this->renderTurboStream(
            $request,
            'module/exporter/domain/artifact/turbo/search.turbo.stream.html.twig',
            [
                'artifacts' => $artifacts,
            ]);
    }
}
