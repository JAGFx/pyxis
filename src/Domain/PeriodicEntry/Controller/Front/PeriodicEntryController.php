<?php

namespace App\Domain\PeriodicEntry\Controller\Front;

use App\Domain\PeriodicEntry\Entity\PeriodicEntry;
use App\Domain\PeriodicEntry\Form\PeriodicEntrySearchType;
use App\Domain\PeriodicEntry\Manager\PeriodicEntryManager;
use App\Domain\PeriodicEntry\Message\Command\PeriodicEntryRemoveCommand;
use App\Domain\PeriodicEntry\Message\Query\PeriodicEntrySearchQuery;
use App\Infrastructure\Turbo\Controller\TurboResponseTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/periodic_entries')]
class PeriodicEntryController extends AbstractController
{
    use TurboResponseTrait;

    public function __construct(
        private readonly PeriodicEntryManager $periodicEntryManager,
    ) {
    }

    #[Route('/{id}/remove', 'front_periodic_entry_remove', requirements: ['id' => '\d+'], methods: Request::METHOD_GET)]
    public function remove(PeriodicEntry $periodicEntry, Request $request): Response
    {
        $this->periodicEntryManager->remove(new PeriodicEntryRemoveCommand($periodicEntry));

        return $this->renderTurboStream($request, 'domain/periodic_entry/turbo/remove.turbo.stream.html.twig', [
            'periodicEntryId' => $periodicEntry->getId(),
        ]);
    }

    #[Route('/search', name: 'front_periodic_entry_search', methods: [Request::METHOD_POST])]
    public function search(Request $request): Response
    {
        $searchQuery = new PeriodicEntrySearchQuery()->setOrderBy('name');

        $this->createForm(PeriodicEntrySearchType::class, $searchQuery)
            ->handleRequest($request);

        $periodicEntries = $this->periodicEntryManager->getPeriodicEntries($searchQuery);

        return $this->renderTurboStream(
            $request,
            'domain/periodic_entry/turbo/search.turbo.stream.html.twig',
            [
                'periodicEntries' => $periodicEntries,
            ]);
    }
}
