<?php

namespace App\Domain\PeriodicEntry\Twig\Components;

use App\Domain\PeriodicEntry\Form\PeriodicEntryCreateOrUpdateType;
use App\Domain\PeriodicEntry\Message\Command\CreateOrUpdatePeriodicEntry\CreateOrUpdatePeriodicEntryCommand;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

/**
 * No initialFormData LiveProp: the command holds a Doctrine Collection (budgets) which cannot be dehydrated.
 * Form values are carried by the form view passed at mount.
 */
#[AsLiveComponent(template: 'domain/periodic_entry/components/PeriodicEntryCreateOrUpdateForm.html.twig')]
class PeriodicEntryCreateOrUpdateForm extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(PeriodicEntryCreateOrUpdateType::class, new CreateOrUpdatePeriodicEntryCommand());
    }
}
