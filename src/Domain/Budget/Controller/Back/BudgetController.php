<?php

namespace App\Domain\Budget\Controller\Back;

use App\Domain\Budget\Entity\Budget;
use App\Domain\Budget\Form\BudgetAccountBalanceType;
use App\Domain\Budget\Form\BudgetCreateOrUpdateType;
use App\Domain\Budget\Message\Command\CreateOrUpdateBudget\CreateOrUpdateBudgetCommand;
use App\Domain\Budget\Message\Query\FindBudgets\FindBudgetsQuery;
use App\Domain\Budget\Security\BudgetVoter;
use App\Infrastructure\Cqs\Bus\SymfonyMessageBus;
use App\Shared\Controller\FormErrorMappingTrait;
use App\Shared\Factory\MenuConfigurationFactory;
use App\Shared\Message\Command\GetBudgetAccountBalanceCommand;
use App\Shared\Operator\BudgetOperator;
use App\Shared\ValueObject\MenuConfigurationEntityEnum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\Exception\ValidationFailedException;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/budgets')]
class BudgetController extends AbstractController
{
    use FormErrorMappingTrait;

    public function __construct(
        private readonly MenuConfigurationFactory $menuConfigurationFactory,
        private readonly BudgetOperator $budgetOperator,
        private readonly ObjectMapperInterface $objectMapper,
        private readonly SymfonyMessageBus $messageBus,
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    #[Route(
        name: 'back_budget_list',
        methods: Request::METHOD_GET
    )]
    public function list(): Response
    {
        $searchQuery = new FindBudgetsQuery()->setOrderBy('name');

        return $this->render('domain/budget/index.html.twig', [
            'budgets' => $this->messageBus->dispatch($searchQuery),
            'config'  => $this->menuConfigurationFactory->createFor(MenuConfigurationEntityEnum::BUDGET),
        ]);
    }

    /**
     * @throws ExceptionInterface
     */
    #[Route(
        '/create',
        name: 'back_budget_create',
        methods: [Request::METHOD_GET, Request::METHOD_POST]
    )]
    public function create(Request $request): Response
    {
        return $this->handleForm($request);
    }

    /**
     * @throws ExceptionInterface
     */
    #[Route(
        '/{id}/update',
        name: 'back_budget_edit',
        requirements: ['id' => Requirement::DIGITS],
        methods: [Request::METHOD_GET, Request::METHOD_POST]
    )]
    #[IsGranted(BudgetVoter::MANAGE, 'budget')]
    public function edit(Request $request, Budget $budget): Response
    {
        return $this->handleForm($request, $budget);
    }

    #[Route(
        '/{id}/balance',
        name: 'back_budget_balance',
        requirements: ['id' => Requirement::DIGITS],
        methods: [Request::METHOD_GET, Request::METHOD_POST]
    )]
    #[IsGranted(BudgetVoter::BALANCE, 'budget')]
    public function balance(Request $request, Budget $budget): Response
    {
        $getBudgetAccountBalanceCommand = new GetBudgetAccountBalanceCommand($budget);

        $form = $this
            ->createForm(BudgetAccountBalanceType::class, $getBudgetAccountBalanceCommand)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->budgetOperator->balancing($getBudgetAccountBalanceCommand);

            return $this->redirectToRoute('back_budget_list');
        }

        return $this->render('domain/budget/balance.html.twig', [
            'form'   => $form,
            'budget' => $budget,
        ]);
    }

    /**
     * @throws ExceptionInterface
     */
    private function handleForm(Request $request, ?Budget $budget = null): Response
    {
        $budgetCommand = is_null($budget)
            ? new CreateOrUpdateBudgetCommand()
            : $this->objectMapper->map($budget, CreateOrUpdateBudgetCommand::class);

        $form = $this->createForm(BudgetCreateOrUpdateType::class, $budgetCommand)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if (!is_null($budget)) {
                    $budgetCommand->setOriginId($budget->getId());
                }

                $this->messageBus->dispatch($budgetCommand);

                return $this->redirectToRoute('back_budget_list');
            } catch (ValidationFailedException $exception) {
                $this->mapBusinessErrorsToForm($exception->getViolations(), $form);
            }
        }

        return $this->render('domain/budget/form.html.twig', [
            'form'   => $form,
            'budget' => $budget,
        ]);
    }
}
