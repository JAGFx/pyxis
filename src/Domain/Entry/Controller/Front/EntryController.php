<?php

namespace App\Domain\Entry\Controller\Front;

use App\Domain\Entry\Entity\Entry;
use App\Domain\Entry\Manager\EntryManager;
use App\Infrastructure\Turbo\Controller\TurboResponseTraits;
use App\Shared\Operator\EntryOperator;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/entries')]
class EntryController extends AbstractController
{
    use TurboResponseTraits;

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
        return $this->renderTurboStream($request, 'domain/entry/turbo/success.stream.balance.html.twig', [
            'amountBalance' => $this->entryOperator->getAmountBalance(),
        ]);
    }

    #[Route('/{id}/remove', name: 'front_entry_remove', methods: Request::METHOD_GET)]
    public function remove(Entry $entry, Request $request): Response
    {
        $entryId = $entry->getId();
        $this->entryManager->remove($entry);

        return $this->renderTurboStream($request, 'domain/entry/turbo/success.stream.remove.html.twig', [
            'entryId' => $entryId,
        ]);
    }
}
