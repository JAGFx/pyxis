<?php

namespace App\Domain\Entry\Controller\Front;

use App\Domain\Entry\Form\EntrySearchType;
use App\Domain\Entry\Message\Command\RemoveEntry\RemoveEntryCommand;
use App\Domain\Entry\Message\Query\FindEntries\FindEntriesQuery;
use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Infrastructure\KnpPaginator\DTO\OrderEnum;
use App\Infrastructure\Turbo\Controller\TurboResponseTrait;
use App\Shared\Operator\EntryOperator;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Throwable;

#[Route('/entries')]
class EntryController extends AbstractController
{
    use TurboResponseTrait;

    public function __construct(
        private readonly EntryOperator $entryOperator,
        private readonly MessageBus    $messageBus,
    ) {
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    #[Route(
        '/balance',
        name: 'front_entry_balance',
        methods: Request::METHOD_GET
    )]
    public function balance(Request $request): Response
    {
        return $this->renderTurboStream($request, 'domain/entry/turbo/balance.turbo.stream.html.twig', [
            'amountBalance' => $this->entryOperator->getAmountBalance(),
        ]);
    }

    /**
     * @throws ExceptionInterface
     * @throws Throwable
     */
    #[Route(
        '/{id}/remove',
        name: 'front_entry_remove',
        requirements: ['id' => Requirement::DIGITS],
        methods: Request::METHOD_GET
    )]
    public function remove(int $id, Request $request): Response
    {
        $this->messageBus->dispatch(new RemoveEntryCommand()->setOriginId($id));

        return $this->renderTurboStream($request, 'domain/entry/turbo/remove.turbo.stream.html.twig', [
            'entryId' => $id,
        ]);
    }

    /**
     * @throws ExceptionInterface
     * @throws Throwable
     */
    #[Route(
        '/search',
        name: 'front_entry_search',
        methods: [Request::METHOD_GET, Request::METHOD_POST]
    )]
    public function search(Request $request): Response
    {
        $searchQuery = new FindEntriesQuery()
            ->setOrderBy('createdAt')
            ->setOrderDirection(OrderEnum::DESC)
        ;

        $this->createForm(EntrySearchType::class, $searchQuery)
            ->submit(array_merge(
                $request->query->all(),
                $request->request->all(),
            ));

        $entries = $this->messageBus->dispatch($searchQuery);

        return $this->renderTurboStream(
            $request,
            'domain/entry/turbo/search.turbo.stream.html.twig',
            [
                'entries' => $entries,
            ]);
    }
}
