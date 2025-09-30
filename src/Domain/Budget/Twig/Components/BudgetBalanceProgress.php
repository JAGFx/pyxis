<?php

namespace App\Domain\Budget\Twig\Components;

use App\Domain\Budget\Form\BudgetBalanceSearchType;
use App\Domain\Budget\Message\Query\GetHistoryAvailableYears\GetHistoryAvailableYearsQuery;
use App\Domain\Budget\ValueObject\BudgetBalanceProgressValueObject;
use App\Infrastructure\Cqs\Bus\MessageBus;
use App\Shared\Message\Query\GetBudgetBalanceProgresses\GetBudgetBalanceProgressesQuery;
use App\Shared\Utils\YearRange;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Throwable;

#[AsLiveComponent(template: 'domain/budget/components/BudgetBalanceProgress.html.twig')]
class BudgetBalanceProgress extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    /**
     * @var BudgetBalanceProgressValueObject[]
     */
    #[LiveProp(useSerializerForHydration: true)]
    public array $budgetBalanceProgresses = [];

    public function __construct(
        private readonly MessageBus $messageBus,
    ) {
    }

    /**
     * @throws ExceptionInterface
     * @throws Throwable
     */
    protected function instantiateForm(): FormInterface
    {
        $getBudgetBalanceProgressesQuery = new GetBudgetBalanceProgressesQuery(YearRange::current(), false);

        /** @var BudgetBalanceProgressValueObject[] $budgetBalanceProgresses */
        $budgetBalanceProgresses       = $this->messageBus->dispatch($getBudgetBalanceProgressesQuery);
        $this->budgetBalanceProgresses = $budgetBalanceProgresses;

        return $this->createForm(BudgetBalanceSearchType::class, $getBudgetBalanceProgressesQuery, [
            'years' => $this->messageBus->dispatch(new GetHistoryAvailableYearsQuery()),
        ]);
    }

    /**
     * @throws Throwable
     * @throws ExceptionInterface
     */
    #[LiveAction]
    public function onYearChange(): void
    {
        $this->submitForm();
        /** @var ?GetBudgetBalanceProgressesQuery $query */
        $query = $this->form?->getData();

        if (null === $query) {
            return;
        }

        /** @var BudgetBalanceProgressValueObject[] $budgetBalanceProgresses */
        $budgetBalanceProgresses       = $this->messageBus->dispatch($query);
        $this->budgetBalanceProgresses = $budgetBalanceProgresses;
    }
}
