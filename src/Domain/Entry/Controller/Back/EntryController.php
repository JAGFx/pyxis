<?php

namespace App\Domain\Entry\Controller\Back;

use App\Domain\Entry\Entity\Entry;
use App\Domain\Entry\Form\EntryPaginationType;
use App\Domain\Entry\Form\EntryType;
use App\Domain\Entry\Manager\EntryManager;
use App\Domain\Entry\Request\EntrySearchRequest;
use App\Domain\Entry\Security\EntryVoter;
use App\Infrastructure\KnpPaginator\Controller\PaginationFormHandlerTrait;
use App\Infrastructure\KnpPaginator\DTO\OrderEnum;
use App\Shared\Factory\MenuConfigurationFactory;
use App\Shared\ValueObject\MenuConfigurationEntityEnum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/entries')]
class EntryController extends AbstractController
{
    use PaginationFormHandlerTrait;
    public const string HANDLE_FORM_CREATE = 'create';
    public const string HANDLE_FORM_UPDATE = 'update';

    public function __construct(
        private readonly EntryManager $entryManager,
        private readonly MenuConfigurationFactory $menuConfigurationFactory,
    ) {
    }

    #[Route(name: 'back_entry_list', methods: Request::METHOD_GET)]
    public function list(Request $request): Response
    {
        $searchRequest = new EntrySearchRequest()
            ->setOrderBy('createdAt')
            ->setOrderDirection(OrderEnum::DESC)
        ;
        $this->handlePaginationForm($request, EntryPaginationType::class, $searchRequest);

        return $this->render('domain/entry/index.html.twig', [
            'entries' => $this->entryManager->getPaginated($searchRequest),
            'config'  => $this->menuConfigurationFactory->createFor(MenuConfigurationEntityEnum::ENTRY),
        ]);
    }

    #[Route('/create', name: 'back_entry_create', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function create(Request $request): Response
    {
        return $this->handleForm(self::HANDLE_FORM_CREATE, $request);
    }

    #[Route('/{id}/update', name: 'back_entry_edit', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    #[IsGranted(EntryVoter::MANAGE, 'entry')]
    public function edit(Entry $entry, Request $request): Response
    {
        return $this->handleForm(self::HANDLE_FORM_UPDATE, $request, $entry);
    }

    private function handleForm(string $type, Request $request, ?Entry $entry = null): Response
    {
        $entry ??= new Entry()
            ->setAmount(0.0)
            ->setName('');

        $form = $this
            ->createForm(EntryType::class, $entry)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (self::HANDLE_FORM_CREATE === $type) {
                $this->entryManager->create($entry);
            } else {
                $this->entryManager->update($entry);
            }

            return $this->redirectToRoute('back_entry_list');
        }

        return $this->render('domain/entry/form.html.twig', [
            'form'  => $form,
            'entry' => $entry,
        ]);
    }
}
