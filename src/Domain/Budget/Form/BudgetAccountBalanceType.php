<?php

namespace App\Domain\Budget\Form;

use App\Domain\Account\Entity\Account;
use App\Domain\Account\Message\Query\AccountSearchQuery;
use App\Domain\Account\Repository\AccountRepository;
use App\Domain\Budget\Request\BudgetAccountBalanceRequest;
use Doctrine\ORM\QueryBuilder;
use Override;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BudgetAccountBalanceType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
                $form = $event->getForm();
                /** @var BudgetAccountBalanceRequest $budgetAccountBalanceRequest */
                $budgetAccountBalanceRequest = $event->getData();

                $form
                    ->add('account', EntityType::class, [
                        'class'         => Account::class,
                        'choice_label'  => 'name',
                        'query_builder' => function (AccountRepository $repository) use ($budgetAccountBalanceRequest): QueryBuilder {
                            $searchQuery = new AccountSearchQuery(true)
                                ->setOrderBy('name')
                                ->setBudget($budgetAccountBalanceRequest->getBudget())
                                ->setPositiveOrNegativeBalance(true)
                            ;

                            return $repository->getAccountsQueryBuilder($searchQuery);
                        },
                    ])
                ;
            })
        ;
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'         => BudgetAccountBalanceRequest::class,
            'label_format'       => 'budget.account_balance.%name%.label',
            'translation_domain' => 'forms',
        ]);
    }
}
