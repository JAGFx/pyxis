<?php

namespace App\Domain\Budget\Twig\Components;

use App\Domain\Budget\Form\BudgetSearchType;
use App\Domain\Budget\Message\Query\FindBudgetsQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(template: 'domain/budget/components/BudgetSearchForm.html.twig')]
class BudgetSearchForm extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(BudgetSearchType::class, new FindBudgetsQuery(), [
            'action' => $this->generateUrl('front_budget_search'),
        ]);
    }
}
