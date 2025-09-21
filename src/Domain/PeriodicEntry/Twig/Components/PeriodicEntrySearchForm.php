<?php

namespace App\Domain\PeriodicEntry\Twig\Components;

use App\Domain\PeriodicEntry\Form\PeriodicEntrySearchType;
use App\Domain\PeriodicEntry\Message\Query\FindPeriodicEntriesQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(template: 'domain/periodic_entry/components/PeriodicEntrySearchForm.html.twig')]
class PeriodicEntrySearchForm extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(PeriodicEntrySearchType::class, new FindPeriodicEntriesQuery(), [
            'action' => $this->generateUrl('front_periodic_entry_search'),
        ]);
    }
}
