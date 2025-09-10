<?php

namespace App\Domain\Entry\Controller\Front;

use App\Domain\Entry\DTO\EntrySearchCommand;
use App\Domain\Entry\Entity\Entry;
use App\Domain\Entry\Form\EntrySearchType;
use App\Domain\Entry\Manager\EntryManager;
use App\Domain\Entry\Security\EntryVoter;
use App\Infrastructure\KnpPaginator\DTO\OrderEnum;
use App\Infrastructure\Turbo\Controller\TurboResponseTrait;
use App\Shared\Operator\EntryOperator;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/entries')]
class EntryController extends AbstractController
{
    use TurboResponseTrait;

    public function __construct(
        private readonly EntryManager $entryManager,
        private readonly EntryOperator $entryOperator,
    ) {
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    #[Route('/balance', name: 'front_entry_balance', methods: Request::METHOD_GET)]
    public function balance(Request $request): Response
    {
        return $this->renderTurboStream($request, 'domain/entry/turbo/balance.turbo.stream.html.twig', [
            'amountBalance' => $this->entryOperator->getAmountBalance(),
        ]);
    }

    #[Route('/{id}/remove', name: 'front_entry_remove', methods: Request::METHOD_GET)]
    #[IsGranted(EntryVoter::MANAGE, 'entry')]
    public function remove(Entry $entry, Request $request): Response
    {
        $entryId = $entry->getId();
        $this->entryManager->remove($entry);

        return $this->renderTurboStream($request, 'domain/entry/turbo/remove.turbo.stream.html.twig', [
            'entryId' => $entryId,
        ]);
    }

    #[Route('/search', name: 'front_entry_search', methods: [Request::METHOD_POST])]
    public function search(Request $request): Response
    {
        $entrySearchCommand = new EntrySearchCommand()
            ->setOrderBy('createdAt')
            ->setOrderDirection(OrderEnum::DESC)
        ;

        $this->createForm(EntrySearchType::class, $entrySearchCommand)
            ->handleRequest($request);

        $entries = $this->entryManager->getPaginated($entrySearchCommand);

        return $this->renderTurboStream(
            $request,
            'domain/entry/turbo/search.turbo.stream.html.twig',
            [
                'entries' => $entries,
            ]);
    }
}
