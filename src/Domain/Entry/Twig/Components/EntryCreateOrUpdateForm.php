<?php

namespace App\Domain\Entry\Twig\Components;

use App\Domain\Entry\Form\EntryCreateOrUpdateType;
use App\Domain\Entry\Message\Command\CreateOrUpdateEntry\CreateOrUpdateEntryCommand;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(template: 'domain/entry/components/EntryCreateOrUpdateForm.html.twig')]
class EntryCreateOrUpdateForm extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp]
    public ?CreateOrUpdateEntryCommand $initialFormData = null;

    protected function instantiateForm(): FormInterface
    {
        $command = $this->initialFormData ?? new CreateOrUpdateEntryCommand();

        return $this->createForm(EntryCreateOrUpdateType::class, $command);
    }
}
