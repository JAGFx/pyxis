<?php

namespace App\Domain\Entry\Form;

use App\Domain\Account\Entity\Account;
use App\Domain\Account\Message\Query\AccountSearchQuery;
use App\Domain\Account\Repository\AccountRepository;
use App\Domain\Budget\Entity\Budget;
use App\Domain\Budget\Repository\BudgetRepository;
use App\Domain\Budget\Request\BudgetSearchRequest;
use App\Domain\Entry\Entity\Entry;
use App\Shared\Form\Type\MoneyType;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('account', EntityType::class, [
                'class'         => Account::class,
                'choice_label'  => 'name',
                'query_builder' => function (AccountRepository $repository): QueryBuilder {
                    $searchQuery = new AccountSearchQuery(true)->setOrderBy('name');

                    return $repository->getAccountsQueryBuilder($searchQuery);
                },
            ])
            ->add('name', TextType::class)
            ->add('amount', MoneyType::class)
            ->add('budget', EntityType::class, [
                'class'         => Budget::class,
                'choice_label'  => 'name',
                'query_builder' => function (BudgetRepository $repository): QueryBuilder {
                    $searchRequest = new BudgetSearchRequest(enabled: true)->setOrderBy('name');

                    return $repository->getBudgetsQueryBuilder($searchRequest);
                },
                'required'    => false,
                'placeholder' => 'entry.form.budget.placeholder',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'         => Entry::class,
            'label_format'       => 'entry.form.%name%.label',
            'translation_domain' => 'forms',
        ]);
    }
}
