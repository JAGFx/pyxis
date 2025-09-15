<?php

namespace App\Domain\Entry\Twig\Components;

use App\Domain\Entry\Form\EntrySearchType;
use App\Domain\Entry\Request\EntrySearchRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(template: 'domain/entry/components/EntrySearchForm.html.twig')]
class EntrySearchForm extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(EntrySearchType::class, new EntrySearchRequest(), [
            'action' => $this->generateUrl('front_entry_search'),
        ]);
    }
}
