<?php

namespace App\Domain\Budget\Controller\Front;

use App\Domain\Account\Entity\Account;
use App\Domain\Budget\Entity\Budget;
use App\Domain\Budget\Form\BudgetBalanceSearchType;
use App\Domain\Budget\Manager\BudgetManager;
use App\Domain\Budget\Manager\HistoryBudgetManager;
use App\Domain\Budget\Model\Search\BudgetSearchCommand;
use App\Domain\Budget\Operator\BudgetOperator;
use App\Domain\Budget\Security\BudgetVoter;
use App\Shared\Model\TurboResponseTraits;
use App\Shared\Operator\EntryOperator;
use App\Shared\Utils\YearRange;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('budgets')]
class BudgetController extends AbstractController
{
    use TurboResponseTraits;

    public function __construct(
        private readonly BudgetManager $budgetManager,
        private readonly BudgetOperator $budgetOperator,
        private readonly HistoryBudgetManager $historyBudgetManager,
        private readonly EntryOperator $entryOperator,
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
            ? $this->budgetManager->searchValueObject($budgetSearchCommand)
            : $this->historyBudgetManager->getHistories($budgetSearchCommand);

        return $this->renderTurboStream(
            $request,
            'domain/budget/turbo/success.stream.progress_list.html.twig',
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
            'domain/budget/turbo/success.stream.toggle.html.twig',
            [
                'budget' => $budget,
            ]
        );
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    #[Route('/{budgetId}/balancing/accounts/{accountId}', name: 'front_budget_balancing', methods: [Request::METHOD_GET])]
    #[IsGranted(BudgetVoter::MANAGE, 'budget')]
    public function balancing(
        Request $request,
        #[MapEntity(mapping: ['budgetId' => 'id'])] Budget $budget,
        #[MapEntity(mapping: ['accountId' => 'id'])] Account $account,
    ): Response {
        $this->budgetManager->balancing($budget, $account);

        $this->addFlash('success', 'Budget équilibré');

        return $this->renderTurboStream(
            $request,
            'domain/budget/turbo/success.stream.balancing.html.twig',
            [
                'budget'        => $budget,
                'amountBalance' => $this->entryOperator->getAmountBalance(),
            ]
        );
    }

    #[Route('/{id}/cash-flow-account', name: 'front_budget_cash_flow_by_account', methods: [Request::METHOD_GET])]
    public function cashFlowByAccount(Request $request, Budget $budget): Response
    {
        return $this->renderTurboStream(
            $request,
            'domain/budget/turbo/success.stream.cash_flow_account.html.twig',
            [
                'budget'    => $budget,
                'cashFlows' => $this->budgetOperator->getBudgetCashFlowsByAccount($budget),
            ]
        );
    }
}
