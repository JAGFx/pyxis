<?php

namespace App\Domain\Budget\Controller\Front;

use App\Domain\Budget\DTO\BudgetSearchCommand;
use App\Domain\Budget\Entity\Budget;
use App\Domain\Budget\Form\BudgetBalanceSearchType;
use App\Domain\Budget\Form\BudgetSearchType;
use App\Domain\Budget\Manager\BudgetManager;
use App\Domain\Budget\Manager\HistoryBudgetManager;
use App\Domain\Budget\Operator\BudgetOperator;
use App\Domain\Budget\Security\BudgetVoter;
use App\Infrastructure\Turbo\Controller\TurboResponseTrait;
use App\Shared\Utils\YearRange;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('budgets')]
class BudgetController extends AbstractController
{
    use TurboResponseTrait;

    public function __construct(
        private readonly BudgetManager $budgetManager,
        private readonly BudgetOperator $budgetOperator,
        private readonly HistoryBudgetManager $historyBudgetManager,
    ) {
    }

    #[Route('/progress-filter', name: 'front_budget_filter', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function filter(Request $request): Response
    {
        $budgetSearchCommand = new BudgetSearchCommand()
            ->setShowCredits(false)
            ->setYear(YearRange::current());

        $years = $this->historyBudgetManager->getAvailableYears();
        $form  = $this
            ->createForm(BudgetBalanceSearchType::class, $budgetSearchCommand, [
                'action' => $this->generateUrl('front_budget_filter'),
                'years'  => $years,
            ])
            ->handleRequest($request);

        $values = (YearRange::current() === $budgetSearchCommand->getYear())
            ? $this->budgetManager->getBudgetValuesObject($budgetSearchCommand)
            : $this->historyBudgetManager->getHistories($budgetSearchCommand);

        return $this->renderTurboStream(
            $request,
            'domain/budget/turbo/progress_list.turbo.stream.html.twig',
            [
                'form'   => $form,
                'values' => $values,
            ]
        );
    }

    #[Route('/{id}/toggle', name: 'front_budget_toggle', methods: [Request::METHOD_GET])]
    #[IsGranted(BudgetVoter::MANAGE, 'budget')]
    public function toggle(Request $request, Budget $budget): Response
    {
        $this->budgetManager->toggle($budget);

        $message = 'Budget ';
        $message .= ($budget->getEnable()) ? 'activé' : 'désactivé';

        $this->addFlash('success', $message);

        return $this->renderTurboStream(
            $request,
            'domain/budget/turbo/toggle.turbo.stream.html.twig',
            [
                'budget' => $budget,
            ]
        );
    }

    #[Route('/{id}/cash-flow-account', name: 'front_budget_cash_flow_by_account', methods: [Request::METHOD_GET])]
    public function cashFlowByAccount(Request $request, Budget $budget): Response
    {
        return $this->renderTurboStream(
            $request,
            'domain/budget/turbo/cash_flow_account.turbo.stream.html.twig',
            [
                'budget'    => $budget,
                'cashFlows' => $this->budgetOperator->getBudgetCashFlowsByAccount($budget),
            ]
        );
    }

    #[Route('/search', name: 'front_budget_search', methods: [Request::METHOD_POST])]
    public function search(Request $request): Response
    {
        $budgetSearchCommand = new BudgetSearchCommand()->setOrderBy('name');

        $this->createForm(BudgetSearchType::class, $budgetSearchCommand)
            ->handleRequest($request);

        $budgets = $this->budgetManager->getBudgets($budgetSearchCommand);

        return $this->renderTurboStream(
            $request,
            'domain/budget/turbo/search.turbo.stream.html.twig',
            [
                'budgets' => $budgets,
            ]);
    }
}
