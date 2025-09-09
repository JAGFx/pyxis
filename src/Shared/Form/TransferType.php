<?php

namespace App\Shared\Form;

use App\Domain\Account\DTO\AccountSearchCommand;
use App\Domain\Account\Entity\Account;
use App\Domain\Account\Repository\AccountRepository;
use App\Domain\Budget\Entity\Budget;
use App\Shared\DTO\Transfer;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('account', EntityType::class, [
                'class'         => Account::class,
                'required'      => false,
                'label'         => 'Name',
                'choice_label'  => 'name',
                'query_builder' => function (AccountRepository $repository): QueryBuilder {
                    $accountSearchCommand = new AccountSearchCommand(true)->setOrderBy('name');

                    return $repository->getAccountsQueryBuilder($accountSearchCommand);
                },
            ])
            ->add('budgetSource', EntityType::class, [
                'class'        => Budget::class,
                'required'     => false,
                'label'        => 'Origine',
                'choice_label' => 'name',
                'row_attr'     => [
                    'class' => 'form-floating',
                ],
            ])
            ->add('budgetTarget', EntityType::class, [
                'class'        => Budget::class,
                'required'     => false,
                'label'        => 'Cible',
                'choice_label' => 'name',
                'row_attr'     => [
                    'class' => 'form-floating',
                ],
            ])
            ->add('amount', MoneyType::class, [
                'label' => 'Valeur',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Transfer::class);
    }
}
