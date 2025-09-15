<?php

namespace App\Domain\Account\Twig\Components;

use App\Domain\Account\Form\AccountSearchType;
use App\Domain\Account\Request\AccountSearchRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(template: 'domain/account/components/AccountSearchForm.html.twig')]
class AccountSearchForm extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(AccountSearchType::class, new AccountSearchRequest(), [
            'action' => $this->generateUrl('front_account_search'),
        ]);
    }
}
