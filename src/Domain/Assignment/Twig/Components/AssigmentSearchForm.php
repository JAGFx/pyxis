<?php

namespace App\Domain\Assignment\Twig\Components;

use App\Domain\Assignment\Form\AssignmentSearchType;
use App\Domain\Assignment\Message\Query\FindAssignments\FindAssignmentsQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(template: 'domain/assigment/components/AssigmentSearchForm.html.twig')]
class AssigmentSearchForm extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(AssignmentSearchType::class, new FindAssignmentsQuery(), [
            'action' => $this->generateUrl('front_assignment_search'),
        ]);
    }
}
