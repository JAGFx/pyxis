<?php

namespace App\Domain\PeriodicEntry\Controller\Back;

use App\Domain\PeriodicEntry\Entity\PeriodicEntry;
use App\Domain\PeriodicEntry\Form\PeriodicEntryCreateOrUpdateType;
use App\Domain\PeriodicEntry\Message\Command\CreateOrUpdatePeriodicEntry\CreateOrUpdatePeriodicEntryCommand;
use App\Domain\PeriodicEntry\Message\Query\FindPeriodicEntries\FindPeriodicEntriesQuery;
use App\Infrastructure\Cqs\Bus\SymfonyMessageBus;
use App\Shared\Controller\FormErrorMappingTrait;
use App\Shared\Factory\MenuConfigurationFactory;
use App\Shared\ValueObject\MenuConfigurationEntityEnum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\Exception\ValidationFailedException;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Throwable;

#[Route('/periodic_entries')]
class PeriodicEntryController extends AbstractController
{
    use FormErrorMappingTrait;

    public function __construct(
        private readonly MenuConfigurationFactory $menuConfigurationFactory,
        private readonly ObjectMapperInterface $objectMapper,
        private readonly SymfonyMessageBus $messageBus,
    ) {
    }

    /**
     * @throws ExceptionInterface
     * @throws Throwable
     */
    #[Route(
        name: 'back_periodic_entry_list',
        methods: Request::METHOD_GET
    )]
    public function list(): Response
    {
        $searchQuery = new FindPeriodicEntriesQuery()->setOrderBy('name');

        return $this->render('domain/periodic_entry/index.html.twig', [
            'periodicEntries' => $this->messageBus->dispatch($searchQuery),
            'config'          => $this->menuConfigurationFactory->createFor(MenuConfigurationEntityEnum::PERIODIC_ENTRY),
        ]);
    }

    /**
     * @throws ExceptionInterface
     * @throws Throwable
     */
    #[Route(
        '/create',
        'back_periodic_entry_create',
        methods: [Request::METHOD_GET, Request::METHOD_POST]
    )]
    public function create(Request $request): Response
    {
        return $this->handleRequest($request);
    }

    /**
     * @throws ExceptionInterface
     * @throws Throwable
     */
    #[Route(
        '/{id}/update',
        'back_periodic_entry_edit',
        requirements: ['id' => Requirement::DIGITS],
        methods: [Request::METHOD_GET, Request::METHOD_POST]
    )]
    public function edit(PeriodicEntry $periodicEntry, Request $request): Response
    {
        return $this->handleRequest($request, $periodicEntry);
    }

    /**
     * @throws ExceptionInterface
     * @throws Throwable
     */
    private function handleRequest(Request $request, ?PeriodicEntry $periodicEntry = null): Response
    {
        $periodicEntryCommand = is_null($periodicEntry)
            ? new CreateOrUpdatePeriodicEntryCommand()
            : $this->objectMapper->map($periodicEntry, CreateOrUpdatePeriodicEntryCommand::class);

        $form = $this
            ->createForm(PeriodicEntryCreateOrUpdateType::class, $periodicEntryCommand)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if (!is_null($periodicEntry)) {
                    $periodicEntryCommand->setOriginId($periodicEntry->getId());
                }

                $this->messageBus->dispatch($periodicEntryCommand);

                return $this->redirectToRoute('back_periodic_entry_list');
            } catch (ValidationFailedException $exception) {
                $this->mapBusinessErrorsToForm($exception->getViolations(), $form);
            }
        }

        return $this->render('domain/periodic_entry/form.html.twig', [
            'form'          => $form,
            'periodicEntry' => $periodicEntry,
        ]);
    }
}
