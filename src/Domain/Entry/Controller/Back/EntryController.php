<?php

namespace App\Domain\Entry\Controller\Back;

use App\Domain\Entry\Entity\Entry;
use App\Domain\Entry\Form\EntrySearchType;
use App\Domain\Entry\Form\EntryType;
use App\Domain\Entry\Manager\EntryManager;
use App\Domain\Entry\Model\EntrySearchCommand;
use App\Infrastructure\KnpPaginator\Controller\PaginationFormHandlerTrait;
use App\Infrastructure\KnpPaginator\Model\OrderEnum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/entries')]
class EntryController extends AbstractController
{
    use PaginationFormHandlerTrait;
    public const string HANDLE_FORM_CREATE = 'create';
    public const string HANDLE_FORM_UPDATE = 'update';

    public function __construct(
        private readonly EntryManager $entryManager,
    ) {
    }

    #[Route('/', name: 'back_entries_list', methods: Request::METHOD_GET)]
    public function list(Request $request): Response
    {
        $entrySearchCommand = new EntrySearchCommand()
            ->setOrderBy('createdAt')
            ->setOrderDirection(OrderEnum::DESC)
        ;
        $this->handlePaginationForm($request, EntrySearchType::class, $entrySearchCommand);

        return $this->render('domain/entry/index.html.twig', [
            'pagination' => $this->entryManager->getPaginated($entrySearchCommand),
        ]);
    }

    #[Route('/create', name: 'back_entry_create', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function create(Request $request): Response
    {
        return $this->handleForm(self::HANDLE_FORM_CREATE, $request, new Entry());
    }

    #[Route('/{id}/update', name: 'back_entry_edit', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function edit(Entry $entry, Request $request): Response
    {
        return $this->handleForm(self::HANDLE_FORM_UPDATE, $request, $entry);
    }

    private function handleForm(string $type, Request $request, Entry $entry): Response
    {
        $form = $this
            ->createForm(EntryType::class, $entry)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (self::HANDLE_FORM_CREATE === $type) {
                $this->entryManager->create($entry);
            } else {
                $this->entryManager->update($entry);
            }

            return $this->redirectToRoute('back_entries_list');
        }

        return $this->render('domain/entry/form.html.twig', [
            'form' => $form,
        ]);
    }
}
