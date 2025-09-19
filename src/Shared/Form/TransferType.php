<?php

namespace App\Shared\Form;

use App\Domain\Account\Entity\Account;
use App\Domain\Account\Message\Query\AccountSearchQuery;
use App\Domain\Account\Repository\AccountRepository;
use App\Domain\Budget\Entity\Budget;
use App\Domain\Budget\Repository\BudgetRepository;
use App\Domain\Budget\Request\BudgetSearchRequest;
use App\Shared\Form\Type\MoneyType;
use App\Shared\Request\TransferRequest;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransferType extends AbstractType
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
            ->add('budget_source', EntityType::class, [
                'property_path' => 'budgetSource',
                'class'         => Budget::class,
                'choice_label'  => 'name',
                'query_builder' => function (BudgetRepository $repository): QueryBuilder {
                    $searchRequest = new BudgetSearchRequest(enabled: true)->setOrderBy('name');

                    return $repository->getBudgetsQueryBuilder($searchRequest);
                },
                'required' => false,
            ])
            ->add('budget_target', EntityType::class, [
                'property_path' => 'budgetTarget',
                'class'         => Budget::class,
                'choice_label'  => 'name',
                'query_builder' => function (BudgetRepository $repository): QueryBuilder {
                    $searchRequest = new BudgetSearchRequest(enabled: true)->setOrderBy('name');

                    return $repository->getBudgetsQueryBuilder($searchRequest);
                },
                'required' => false,
            ])
            ->add('amount', MoneyType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'         => TransferRequest::class,
            'label_format'       => 'shared.transfer.%name%.label',
            'translation_domain' => 'forms',
        ]);
    }
}
