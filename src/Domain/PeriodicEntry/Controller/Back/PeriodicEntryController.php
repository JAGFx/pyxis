<?php

namespace App\Domain\PeriodicEntry\Controller\Back;

use App\Domain\PeriodicEntry\Entity\PeriodicEntry;
use App\Domain\PeriodicEntry\Form\PeriodicEntryType;
use App\Domain\PeriodicEntry\Manager\PeriodicEntryManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/periodic_entries')]
class PeriodicEntryController extends AbstractController
{
    private const HANDLE_FORM_CREATE = 'create';
    private const HANDLE_FORM_UPDATE = 'update';

    public function __construct(
        private readonly PeriodicEntryManager $periodicEntryManager,
    ) {
    }

    #[Route(name: 'back_periodicentry_list', methods: Request::METHOD_GET)]
    public function list(): Response
    {
        return $this->render('domain/periodic_entry/index.html.twig', [
            'periodicEntries' => $this->periodicEntryManager->getPeriodicEntries(),
        ]);
    }

    #[Route('/create', 'back_periodicentry_create', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function create(Request $request): Response
    {
        return $this->handleRequest(self::HANDLE_FORM_CREATE, $request, new PeriodicEntry());
    }

    #[Route('/{id}/update', 'back_periodicentry_edit', requirements: ['id' => '\d+'], methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function edit(PeriodicEntry $periodicEntry, Request $request): Response
    {
        return $this->handleRequest(self::HANDLE_FORM_UPDATE, $request, $periodicEntry);
    }

    private function handleRequest(string $type, Request $request, PeriodicEntry $periodicEntry): Response
    {
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
            'form' => $form,
        ]);
    }
}
