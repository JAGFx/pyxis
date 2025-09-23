<?php

namespace App\Domain\Budget\Twig\Components;

use App\Domain\Budget\Form\BudgetBalanceSearchType;
use App\Domain\Budget\Message\Query\FindBudgets\FindBudgetsQuery;
use App\Domain\Budget\Message\Query\GetHistoryAvailableYears\GetHistoryAvailableYearsQuery;
use App\Domain\Budget\ValueObject\BudgetBalanceProgressValueObject;
use App\Shared\Cqs\Bus\MessageBus;
use App\Shared\Operator\BudgetOperator;
use App\Shared\Utils\YearRange;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(template: 'domain/budget/components/BudgetBalanceProgress.html.twig')]
class BudgetBalanceProgress extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp(useSerializerForHydration: true)]
    /** @var BudgetBalanceProgressValueObject[] */
    public array $budgetBalanceProgresses = [];

    public function __construct(
        private readonly BudgetOperator $budgetOperator,
        private readonly MessageBus $messageBus,
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    protected function instantiateForm(): FormInterface
    {
        $searchQuery = new FindBudgetsQuery()
            ->setShowCredits(false)
            ->setYear(YearRange::current());

        $this->budgetBalanceProgresses = $this->budgetOperator->getBudgetBalanceProgresses($searchQuery);

        return $this->createForm(BudgetBalanceSearchType::class, $searchQuery, [
            'years' => $this->messageBus->dispatch(new GetHistoryAvailableYearsQuery()),
        ]);
    }

    #[LiveAction]
    public function onYearChange(): void
    {
        $this->submitForm();
        /** @var ?FindBudgetsQuery $budgetSearchCommand */
        $budgetSearchCommand = $this->form?->getData();

        if (null === $budgetSearchCommand) {
            return;
        }

        $this->budgetBalanceProgresses = $this->budgetOperator->getBudgetBalanceProgresses($budgetSearchCommand);
    }
}
