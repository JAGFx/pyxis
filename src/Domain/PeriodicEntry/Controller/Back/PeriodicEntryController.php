<?php

namespace App\Domain\PeriodicEntry\Controller\Back;

use App\Domain\PeriodicEntry\DTO\PeriodicEntrySearchCommand;
use App\Domain\PeriodicEntry\Entity\PeriodicEntry;
use App\Domain\PeriodicEntry\Form\PeriodicEntryType;
use App\Domain\PeriodicEntry\Manager\PeriodicEntryManager;
use App\Shared\Factory\MenuConfigurationFactory;
use App\Shared\ValueObject\MenuConfigurationEntityEnum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/periodic_entries')]
class PeriodicEntryController extends AbstractController
{
    private const string HANDLE_FORM_CREATE = 'create';
    private const string HANDLE_FORM_UPDATE = 'update';

    public function __construct(
        private readonly PeriodicEntryManager $periodicEntryManager,
        private readonly MenuConfigurationFactory $menuConfigurationFactory,
    ) {
    }

    #[Route(name: 'back_periodicentry_list', methods: Request::METHOD_GET)]
    public function list(): Response
    {
        $periodicEntrySearchCommand = new PeriodicEntrySearchCommand()->setOrderBy('name');

        return $this->render('domain/periodic_entry/index.html.twig', [
            'periodicEntries' => $this->periodicEntryManager->getPeriodicEntries($periodicEntrySearchCommand),
            'config'          => $this->menuConfigurationFactory->createFor(MenuConfigurationEntityEnum::PERIODIC_ENTRY),
        ]);
    }

    #[Route('/create', 'back_periodicentry_create', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function create(Request $request): Response
    {
        return $this->handleRequest(self::HANDLE_FORM_CREATE, $request);
    }

    #[Route('/{id}/update', 'back_periodicentry_edit', requirements: ['id' => '\d+'], methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function edit(PeriodicEntry $periodicEntry, Request $request): Response
    {
        return $this->handleRequest(self::HANDLE_FORM_UPDATE, $request, $periodicEntry);
    }

    private function handleRequest(string $type, Request $request, ?PeriodicEntry $periodicEntry = null): Response
    {
        $periodicEntry ??= new PeriodicEntry()->setName('');

        $form = $this
            ->createForm(PeriodicEntryType::class, $periodicEntry)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (self::HANDLE_FORM_CREATE === $type) {
                $this->periodicEntryManager->create($periodicEntry);
            } else {
                $this->periodicEntryManager->update($periodicEntry);
            }

            return $this->redirectToRoute('back_periodicentry_list');
        }

        return $this->render('domain/periodic_entry/form.html.twig', [
            'form'          => $form,
            'periodicEntry' => $periodicEntry,
        ]);
    }
}
