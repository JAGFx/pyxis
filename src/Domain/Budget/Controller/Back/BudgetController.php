<?php

namespace App\Domain\Budget\Controller\Back;

use App\Domain\Budget\Entity\Budget;
use App\Domain\Budget\Form\BudgetAccountBalanceType;
use App\Domain\Budget\Form\BudgetCreateOrUpdateType;
use App\Domain\Budget\Message\Command\CreateOrUpdateBudget\CreateOrUpdateBudgetCommand;
use App\Domain\Budget\Message\Query\FindBudgets\FindBudgetsQuery;
use App\Domain\Budget\Security\BudgetVoter;
use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Infrastructure\Cqs\Controller\FormErrorMappingTrait;
use App\Shared\MenuConfiguration\Enum\MenuConfigurationEntityEnum;
use App\Shared\MenuConfiguration\Factory\MenuConfigurationFactory;
use App\Shared\Message\Command\ApplyBudgetAccountBalance\ApplyBudgetAccountBalanceCommand;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\Exception\ValidationFailedException;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

#[Route('/budgets')]
class BudgetController extends AbstractController
{
    use FormErrorMappingTrait;

    public function __construct(
        private readonly MenuConfigurationFactory $menuConfigurationFactory,
        private readonly ObjectMapperInterface $objectMapper,
        private readonly MessageBus $messageBus,
    ) {
    }

    /**
     * @throws ExceptionInterface
     * @throws Throwable
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
     * @throws Throwable
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
     * @throws Throwable
     */
    #[Route(
        '/{id}/update',
        name: 'back_budget_edit',
        requirements: ['id' => Requirement::DIGITS],
        methods: [Request::METHOD_GET, Request::METHOD_POST]
    )]
    public function edit(Request $request, Budget $budget): Response
    {
        return $this->handleForm($request, $budget);
    }

    /**
     * @throws ExceptionInterface
     * @throws Throwable
     */
    #[Route(
        '/{id}/balance',
        name: 'back_budget_balance',
        requirements: ['id' => Requirement::DIGITS],
        methods: [Request::METHOD_GET, Request::METHOD_POST]
    )]
    #[IsGranted(BudgetVoter::BALANCE, 'budget')]
    public function balance(Request $request, Budget $budget): Response
    {
        $applyBudgetAccountBalanceCommand = new ApplyBudgetAccountBalanceCommand($budget);

        $form = $this
            ->createForm(BudgetAccountBalanceType::class, $applyBudgetAccountBalanceCommand)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->messageBus->dispatch($applyBudgetAccountBalanceCommand);

            return $this->redirectToRoute('back_budget_list');
        }

        return $this->render('domain/budget/balance.html.twig', [
            'form'   => $form,
            'budget' => $budget,
        ]);
    }

    /**
     * @throws ExceptionInterface
     * @throws Throwable
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
