<?php

declare(strict_types=1);

namespace App\Module\Exporter\Domain\Artifact\Controller\Back;

use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Infrastructure\KnpPaginator\Controller\PaginationFormHandlerTrait;
use App\Infrastructure\KnpPaginator\DTO\OrderEnum;
use App\Module\Exporter\Domain\Artifact\Form\ArtifactSearchType;
use App\Module\Exporter\Domain\Artifact\Message\Query\DownloadArtifact\DownloadArtifactQuery;
use App\Module\Exporter\Domain\Artifact\Message\Query\FindArtifacts\FindArtifactsQuery;
use App\Module\Exporter\Infrastructure\Document\Model\DocumentTypeEnum;
use App\Module\Exporter\Infrastructure\RequestExport\Factory\RequestExportCommandFactory;
use App\Module\Exporter\Infrastructure\RequestExport\Message\Command\AbstractRequestExportCommand;
use App\Shared\MenuConfiguration\Enum\MenuConfigurationEntityEnum;
use App\Shared\MenuConfiguration\Factory\MenuConfigurationFactory;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Uid\Uuid;
use Throwable;

#[Route('/exporter')]
class ArtifactController extends AbstractController
{
    use PaginationFormHandlerTrait;

    public function __construct(
        private readonly MessageBus $messageBus,
        private readonly MenuConfigurationFactory $menuConfigurationFactory,
        private readonly RequestExportCommandFactory $requestExportCommandFactory,
    ) {
    }

    /**
     * @throws Throwable
     * @throws ExceptionInterface
     */
    #[Route(
        '/requests/list',
        name: 'back_exporter_list_artifacts',
        methods: Request::METHOD_GET
    )]
    public function list(Request $request): Response
    {
        $searchQuery = new FindArtifactsQuery()
            ->setOrderBy('createdAt')
            ->setOrderDirection(OrderEnum::DESC)
        ;

        $this->handlePaginationForm($request, ArtifactSearchType::class, $searchQuery);

        return $this->render('module/exporter/domain/artifact/index.html.twig', [
            'artifacts' => $this->messageBus->dispatch($searchQuery),
            'config'    => $this->menuConfigurationFactory->createFor(MenuConfigurationEntityEnum::ARTIFACT),
        ]);
    }

    /**
     * @param array<string, string> $filters
     *
     * @throws Throwable
     * @throws ExceptionInterface
     */
    #[Route('/requests/request', name: 'back_exporter_request', methods: [Request::METHOD_GET])]
    public function request(
        #[MapQueryParameter]
        string $target,
        #[MapQueryParameter(AbstractRequestExportCommand::FILTERS_KEY)]
        array $filters = [],
        #[MapQueryParameter]
        DocumentTypeEnum $type = DocumentTypeEnum::CSV,
    ): Response {
        try {
            $command = $this->requestExportCommandFactory->create($target);
        } catch (InvalidArgumentException $invalidArgumentException) {
            throw new BadRequestHttpException($invalidArgumentException->getMessage(), $invalidArgumentException);
        }

        $command->setDocumentType($type)->setFilters($filters);
        $this->messageBus->dispatch($command);

        return $this->redirectToRoute('back_exporter_list_artifacts');
    }

    /**
     * @throws Throwable
     * @throws ExceptionInterface
     */
    #[Route(
        '/requests/{artifactUuid}/download',
        name: 'back_exporter_download_artifact',
        requirements: ['artifactUuid' => Requirement::UUID_V7],
        methods: Request::METHOD_GET
    )]
    public function download(Uuid $artifactUuid): StreamedResponse
    {
        /** @var StreamedResponse $streamedResponse */
        $streamedResponse = $this->messageBus->dispatch(new DownloadArtifactQuery($artifactUuid));

        return $streamedResponse;
    }
}
