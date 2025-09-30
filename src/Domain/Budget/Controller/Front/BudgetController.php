<?php

namespace App\Domain\Budget\Controller\Front;

use App\Domain\Budget\Entity\Budget;
use App\Domain\Budget\Form\BudgetSearchType;
use App\Domain\Budget\Message\Command\ToggleEnableBudget\ToggleEnableBudgetCommand;
use App\Domain\Budget\Message\Query\FindBudgets\FindBudgetsQuery;
use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Infrastructure\Turbo\Controller\TurboResponseTrait;
use App\Shared\Message\Query\GetBudgetCashFlowsByAccount\GetBudgetCashFlowsByAccountQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Throwable;

#[Route('budgets')]
class BudgetController extends AbstractController
{
    use TurboResponseTrait;

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
        name: 'front_budget_toggle',
        requirements: ['id' => Requirement::DIGITS],
        methods: [Request::METHOD_GET]
    )]
    public function toggle(Request $request, Budget $budget): Response
    {
        $this->messageBus->dispatch(new ToggleEnableBudgetCommand()->setOriginId($budget->getId()));

        $message = 'Budget ';
        $message .= ($budget->isEnabled()) ? 'activé' : 'désactivé';

        $this->addFlash('success', $message);

        return $this->renderTurboStream(
            $request,
            'domain/budget/turbo/toggle.turbo.stream.html.twig',
            [
                'budget' => $budget,
            ]
        );
    }

    #[Route(
        '/{id}/cash-flow-account',
        name: 'front_budget_cash_flow_by_account',
        requirements: ['id' => Requirement::DIGITS],
        methods: [Request::METHOD_GET]
    )]
    public function cashFlowByAccount(Request $request, Budget $budget): Response
    {
        return $this->renderTurboStream(
            $request,
            'domain/budget/turbo/cash_flow_account.turbo.stream.html.twig',
            [
                'budget'    => $budget,
                'cashFlows' => $this->messageBus->dispatch(new GetBudgetCashFlowsByAccountQuery($budget->getId())),
            ]
        );
    }

    /**
     * @throws ExceptionInterface
     * @throws Throwable
     */
    #[Route(
        '/search',
        name: 'front_budget_search',
        methods: [Request::METHOD_POST]
    )]
    public function search(Request $request): Response
    {
        $searchQuery = new FindBudgetsQuery()->setOrderBy('name');

        $this->createForm(BudgetSearchType::class, $searchQuery)
            ->handleRequest($request);

        $budgets = $this->messageBus->dispatch($searchQuery);

        return $this->renderTurboStream(
            $request,
            'domain/budget/turbo/search.turbo.stream.html.twig',
            [
                'budgets' => $budgets,
            ]);
    }
}
