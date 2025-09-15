<?php

namespace App\Domain\Budget\Controller\Back;

use App\Domain\Budget\Entity\Budget;
use App\Domain\Budget\Form\BudgetAccountBalanceType;
use App\Domain\Budget\Form\BudgetType;
use App\Domain\Budget\Manager\BudgetManager;
use App\Domain\Budget\Request\BudgetAccountBalanceRequest;
use App\Domain\Budget\Request\BudgetSearchRequest;
use App\Domain\Budget\Security\BudgetVoter;
use App\Shared\Controller\ControllerActionEnum;
use App\Shared\Factory\MenuConfigurationFactory;
use App\Shared\Operator\BudgetOperator;
use App\Shared\ValueObject\MenuConfigurationEntityEnum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/budgets')]
class BudgetController extends AbstractController
{
    public function __construct(
        private readonly BudgetManager $budgetManager,
        private readonly MenuConfigurationFactory $menuConfigurationFactory, private readonly BudgetOperator $budgetOperator,
    ) {
    }

    #[Route(name: 'back_budget_list', methods: Request::METHOD_GET)]
    public function list(): Response
    {
        $searchRequest = new BudgetSearchRequest()->setOrderBy('name');

        return $this->render('domain/budget/index.html.twig', [
            'budgets' => $this->budgetManager->getBudgets($searchRequest),
            'config'  => $this->menuConfigurationFactory->createFor(MenuConfigurationEntityEnum::BUDGET),
        ]);
    }

    #[Route('/create', name: 'back_budget_create', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function create(Request $request): Response
    {
        return $this->handleForm(ControllerActionEnum::CREATE, $request);
    }

    #[Route('/{id}/update', name: 'back_budget_edit', requirements: ['id' => '\d+'], methods: [Request::METHOD_GET, Request::METHOD_POST])]
    #[IsGranted(BudgetVoter::MANAGE, 'budget')]
    public function edit(Request $request, Budget $budget): Response
    {
        return $this->handleForm(ControllerActionEnum::EDIT, $request, $budget);
    }

    #[Route('/{id}/balance', name: 'back_budget_balance', requirements: ['id' => '\d+'], methods: [Request::METHOD_GET, Request::METHOD_POST])]
    #[IsGranted(BudgetVoter::BALANCE, 'budget')]
    public function balance(Request $request, Budget $budget): Response
    {
        $budgetAccountBalanceRequest = new BudgetAccountBalanceRequest($budget);

        $form = $this
            ->createForm(BudgetAccountBalanceType::class, $budgetAccountBalanceRequest)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->budgetOperator->balancing($budgetAccountBalanceRequest);

            return $this->redirectToRoute('back_budget_list');
        }

        return $this->render('domain/budget/balance.html.twig', [
            'form'   => $form,
            'budget' => $budget,
        ]);
    }

    private function handleForm(ControllerActionEnum $action, Request $request, ?Budget $budget = null): Response
    {
        $budget ??= new Budget()->setName('');

        $form = $this->createForm(BudgetType::class, $budget)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (ControllerActionEnum::CREATE === $action) {
                $this->budgetManager->create($budget);
            } else {
                $this->budgetManager->update();
            }

            return $this->redirectToRoute('back_budget_list');
        }

        return $this->render('domain/budget/form.html.twig', [
            'form'   => $form,
            'budget' => $budget,
        ]);
    }
}
