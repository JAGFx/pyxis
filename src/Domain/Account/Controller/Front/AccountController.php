<?php

declare(strict_types=1);

namespace App\Domain\Account\Controller\Front;

use App\Domain\Account\Entity\Account;
use App\Domain\Account\Form\AccountSearchType;
use App\Domain\Account\Message\Command\ToggleEnableAccount\ToggleEnableAccountCommand;
use App\Domain\Account\Message\Query\FindAccountIds\FindAccountIdsQuery;
use App\Domain\Account\Message\Query\FindAccounts\FindAccountsQuery;
use App\Domain\Account\ValueObject\AccountIds;
use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Infrastructure\Turbo\Controller\TurboResponseTrait;
use App\Shared\Message\Query\GetAmountBalance\GetAmountBalanceQuery;
use App\Shared\ValueObject\AmountBalance;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Throwable;

#[Route('accounts')]
class AccountController extends AbstractController
{
    use TurboResponseTrait;

    private const int MAX_ACCOUNTS_CASH_FLOW = 3;

    public function __construct(
        private readonly MessageBus $messageBus,
    ) {
    }

    /**
     * @throws ExceptionInterface
     * @throws Throwable
     */
    #[Route(
        '/{id}/toggle',
        name: 'front_account_toggle',
        requirements: ['id' => Requirement::DIGITS],
        methods: [Request::METHOD_GET]
    )]
    public function toggle(Request $request, Account $account): Response
    {
        $this->messageBus->dispatch(new ToggleEnableAccountCommand()->setOriginId($account->getId()));

        $message = 'Compte ';
        $message .= ($account->isEnabled()) ? 'activé' : 'désactivé';

        $this->addFlash('success', $message);

        return $this->renderTurboStream(
            $request,
            'domain/account/turbo/toggle.turbo.stream.html.twig',
            [
                'account' => $account,
            ]
        );
    }

    /**
     * @throws ExceptionInterface
     * @throws Throwable
     */
    #[Route(
        '/{id}/cash-flow',
        name: 'front_account_cash_flow',
        requirements: ['id' => Requirement::DIGITS],
        methods: [Request::METHOD_GET]
    )]
    public function cashFlow(Request $request, Account $account): Response
    {
        /** @var AmountBalance[] $amountBalances */
        $amountBalances = $this->messageBus->dispatch(new GetAmountBalanceQuery([$account->getId()]));
        /** @var AmountBalance $amountBalance */
        $amountBalance = reset($amountBalances);

        return $this->renderTurboStream(
            $request,
            'domain/account/turbo/cash_flow_account.turbo.stream.html.twig',
            [
                'account'       => $account,
                'amountBalance' => $amountBalance,
            ]
        );
    }

    /**
     * @throws ExceptionInterface
     * @throws Throwable
     */
    #[Route(
        '/search',
        name: 'front_account_search',
        methods: [Request::METHOD_POST]
    )]
    public function search(Request $request): Response
    {
        $searchQuery = new FindAccountsQuery()->setOrderBy('name');

        $this->createForm(AccountSearchType::class, $searchQuery)
            ->handleRequest($request);

        $accounts = $this->messageBus->dispatch($searchQuery);

        return $this->renderTurboStream(
            $request,
            'domain/account/turbo/search.turbo.stream.html.twig',
            [
                'accounts' => $accounts,
            ]);
    }

    /**
     * @throws Throwable
     * @throws ExceptionInterface
     */
    #[Route(
        '/cash-flow-all',
        name: 'front_account_cash_flow_all',
        methods: [Request::METHOD_GET]
    )]
    public function cashFlowAllAccount(Request $request): Response
    {
        /** @var AccountIds $accountIds */
        $accountIds = $this->messageBus->dispatch(new FindAccountIdsQuery(self::MAX_ACCOUNTS_CASH_FLOW));

        return $this->renderTurboStream($request, 'domain/entry/turbo/balance_by_account.stream.html.twig', [
            'amountBalances' => $this->messageBus->dispatch(new GetAmountBalanceQuery($accountIds->getIds())),
            'hasMore'        => $accountIds->getTotal() > self::MAX_ACCOUNTS_CASH_FLOW,
        ]);
    }
}
