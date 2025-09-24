<?php

namespace App\Domain\Entry\Controller\Back;

use App\Domain\Entry\Entity\Entry;
use App\Domain\Entry\Form\EntryCreateOrUpdateType;
use App\Domain\Entry\Form\EntrySearchType;
use App\Domain\Entry\Message\Command\CreateOrUpdateEntry\CreateOrUpdateEntryCommand;
use App\Domain\Entry\Message\Query\FindEntries\FindEntriesQuery;
use App\Domain\Entry\Security\EntryVoter;
use App\Infrastructure\KnpPaginator\Controller\PaginationFormHandlerTrait;
use App\Infrastructure\KnpPaginator\DTO\OrderEnum;
use App\Shared\Controller\FormErrorMappingTrait;
use App\Shared\Cqs\Bus\MessageBus;
use App\Shared\Factory\MenuConfigurationFactory;
use App\Shared\ValueObject\MenuConfigurationEntityEnum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\Exception\ValidationFailedException;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/entries')]
class EntryController extends AbstractController
{
    use PaginationFormHandlerTrait;
    use FormErrorMappingTrait;

    public function __construct(
        private readonly MenuConfigurationFactory $menuConfigurationFactory,
        private readonly ObjectMapperInterface $objectMapper,
        private readonly MessageBus $messageBus,
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    #[Route(name: 'back_entry_list', methods: Request::METHOD_GET)]
    public function list(Request $request): Response
    {
        $searchQuery = new FindEntriesQuery()
            ->setOrderBy('createdAt')
            ->setOrderDirection(OrderEnum::DESC)
        ;
        $this->handlePaginationForm($request, EntrySearchType::class, $searchQuery);

        return $this->render('domain/entry/index.html.twig', [
            'entries' => $this->messageBus->dispatch($searchQuery),
            'config'  => $this->menuConfigurationFactory->createFor(MenuConfigurationEntityEnum::ENTRY),
        ]);
    }

    /**
     * @throws ExceptionInterface
     */
    #[Route('/create', name: 'back_entry_create', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function create(Request $request): Response
    {
        return $this->handleForm($request);
    }

    /**
     * @throws ExceptionInterface
     */
    #[Route('/{id}/update', name: 'back_entry_edit', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    // TODO: add requirement and multiline parameters
    #[IsGranted(EntryVoter::MANAGE, 'entry')]
    public function edit(Entry $entry, Request $request): Response
    {
        return $this->handleForm($request, $entry);
    }

    /**
     * @throws ExceptionInterface
     */
    private function handleForm(Request $request, ?Entry $entry = null): Response
    {
        $entryCommand = is_null($entry)
            ? new CreateOrUpdateEntryCommand()
            : $this->objectMapper->map($entry, CreateOrUpdateEntryCommand::class);

        $form = $this
            ->createForm(EntryCreateOrUpdateType::class, $entryCommand)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if (!is_null($entry)) {
                    $entryCommand->setOriginId($entry->getId());
                }

                $this->messageBus->dispatch($entryCommand);

                return $this->redirectToRoute('back_entry_list');
            } catch (ValidationFailedException $exception) {
                $this->mapBusinessErrorsToForm($exception->getViolations(), $form);
            }
        }

        return $this->render('domain/entry/form.html.twig', [
            'form'  => $form,
            'entry' => $entry,
        ]);
    }
}
