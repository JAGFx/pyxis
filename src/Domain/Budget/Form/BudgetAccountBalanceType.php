<?php

namespace App\Domain\Budget\Form;

use App\Domain\Account\Entity\Account;
use App\Domain\Account\Message\Query\FindAccountsQuery;
use App\Domain\Account\Repository\AccountRepository;
use App\Shared\Message\Command\GetBudgetAccountBalanceCommand;
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
                /** @var GetBudgetAccountBalanceCommand $budgetAccountBalanceCommand */
                $budgetAccountBalanceCommand = $event->getData();

                $form
                    ->add('account', EntityType::class, [
                        'class'         => Account::class,
                        'choice_label'  => 'name',
                        'query_builder' => function (AccountRepository $repository) use ($budgetAccountBalanceCommand): QueryBuilder {
                            $searchQuery = new FindAccountsQuery(true)
                                ->setOrderBy('name')
                                ->setBudget($budgetAccountBalanceCommand->getBudget())
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
            'data_class'         => GetBudgetAccountBalanceCommand::class,
            'label_format'       => 'budget.account_balance.%name%.label',
            'translation_domain' => 'forms',
        ]);
    }
}
