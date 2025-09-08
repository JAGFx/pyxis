<?php

declare(strict_types=1);

namespace App\Domain\Account\Controller\Front;

use App\Domain\Account\DTO\AccountSearchCommand;
use App\Domain\Account\Entity\Account;
use App\Domain\Account\Form\AccountSearchType;
use App\Domain\Account\Manager\AccountManager;
use App\Domain\Account\Security\AccountVoter;
use App\Infrastructure\Turbo\Controller\TurboResponseTrait;
use App\Shared\Operator\EntryOperator;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('accounts')]
class AccountController extends AbstractController
{
    use TurboResponseTrait;

    public function __construct(
        private readonly AccountManager $accountManager,
        private readonly EntryOperator $entryOperator,
    ) {
    }

    #[Route('/{id}/toggle', name: 'front_account_toggle', methods: [Request::METHOD_GET])]
    public function toggle(Request $request, Account $account): Response
    {
        if ($account->isEnable()) {
            $this->denyAccessUnlessGranted(AccountVoter::DISABLE, $account);
        }

        $this->accountManager->toggle($account);

        $message = 'Compte ';
        $message .= ($account->isEnable()) ? 'activé' : 'désactivé';

        $this->addFlash('success', $message);

        return $this->renderTurboStream(
            $request,
            'domain/account/turbo/success.stream.toggle.html.twig',
            [
                'account' => $account,
            ]
        );
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    #[Route('/{id}/cash-flow', name: 'front_account_cash_flow', methods: [Request::METHOD_GET])]
    public function cashFlow(Request $request, Account $account): Response
    {
        $amountBalance = $this->entryOperator->getAmountBalance($account);

        return $this->renderTurboStream(
            $request,
            'domain/account/turbo/success.stream.cash_flow_account.html.twig',
            [
                'account'       => $account,
                'amountBalance' => $amountBalance,
            ]
        );
    }

    #[Route('/search-and-filter-form', name: 'front_account_search_and_filter_form', methods: [Request::METHOD_GET])]
    public function searchAndFilterForm(Request $request): Response
    {
        $form = $this->createForm(AccountSearchType::class);

        return $this->renderTurboStream(
            $request,
            'domain/account/turbo/success.stream.search_and_filter_form.html.twig',
            [
                'form' => $form,
            ]);
    }

    #[Route('/search-and-filter', name: 'front_account_search_and_filter', methods: [Request::METHOD_POST])]
    public function searchAndFilter(Request $request): Response
    {
        $accountSearchCommand = new AccountSearchCommand();
        $this->createForm(AccountSearchType::class, $accountSearchCommand)
            ->handleRequest($request);

        $accounts = $this->accountManager->getAccounts($accountSearchCommand);

        return $this->renderTurboStream(
            $request,
            'domain/account/turbo/success.stream.search_and_filter.html.twig',
            [
                'accounts' => $accounts,
            ]);
    }
}
