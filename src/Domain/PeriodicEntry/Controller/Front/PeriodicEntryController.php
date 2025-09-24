<?php

namespace App\Domain\PeriodicEntry\Controller\Front;

use App\Domain\PeriodicEntry\Entity\PeriodicEntry;
use App\Domain\PeriodicEntry\Form\PeriodicEntrySearchType;
use App\Domain\PeriodicEntry\Message\Command\RemovePeriodicEntry\RemovePeriodicEntryCommand;
use App\Domain\PeriodicEntry\Message\Query\FindPeriodicEntries\FindPeriodicEntriesQuery;
use App\Infrastructure\Turbo\Controller\TurboResponseTrait;
use App\Shared\Cqs\Bus\MessageBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/periodic_entries')]
class PeriodicEntryController extends AbstractController
{
    use TurboResponseTrait;

    public function __construct(
        private readonly MessageBus $messageBus,
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    #[Route('/{id}/remove', 'front_periodic_entry_remove', requirements: ['id' => '\d+'], methods: Request::METHOD_GET)]
    public function remove(PeriodicEntry $periodicEntry, Request $request): Response
    {
        $this->messageBus->dispatch(
            new RemovePeriodicEntryCommand()->setOriginId($periodicEntry->getId())
        );

        return $this->renderTurboStream($request, 'domain/periodic_entry/turbo/remove.turbo.stream.html.twig', [
            'periodicEntryId' => $periodicEntry->getId(),
        ]);
    }

    /**
     * @throws ExceptionInterface
     */
    #[Route('/search', name: 'front_periodic_entry_search', methods: [Request::METHOD_POST])]
    public function search(Request $request): Response
    {
        $searchQuery = new FindPeriodicEntriesQuery()->setOrderBy('name');

        $this->createForm(PeriodicEntrySearchType::class, $searchQuery)
            ->handleRequest($request);

        $periodicEntries = $this->messageBus->dispatch($searchQuery);

        return $this->renderTurboStream(
            $request,
            'domain/periodic_entry/turbo/search.turbo.stream.html.twig',
            [
                'periodicEntries' => $periodicEntries,
            ]);
    }
}
