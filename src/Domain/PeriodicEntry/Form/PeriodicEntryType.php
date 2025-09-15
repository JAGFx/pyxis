<?php

namespace App\Domain\PeriodicEntry\Form;

use App\Domain\Account\DTO\AccountSearchCommand;
use App\Domain\Account\Entity\Account;
use App\Domain\Account\Repository\AccountRepository;
use App\Domain\Budget\DTO\BudgetSearchCommand;
use App\Domain\Budget\Entity\Budget;
use App\Domain\Budget\Repository\BudgetRepository;
use App\Domain\PeriodicEntry\Entity\PeriodicEntry;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PeriodicEntryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('account', EntityType::class, [
                'class'         => Account::class,
                'choice_label'  => 'name',
                'query_builder' => function (AccountRepository $repository): QueryBuilder {
                    $accountSearchCommand = new AccountSearchCommand(true)->setOrderBy('name');

                    return $repository->getAccountsQueryBuilder($accountSearchCommand);
                },
            ])
            ->add('name', TextType::class)
            ->add('amount', MoneyType::class, [
                'required' => false,
            ])
            ->add('execution_date', DateType::class, [
                'property_path' => 'executionDate',
                'widget'        => 'single_text',
                'input'         => 'datetime_immutable',
            ])
            ->add('budgets', EntityType::class, [
                'class'         => Budget::class,
                'multiple'      => true,
                'expanded'      => false,
                'choice_label'  => 'name',
                'required'      => false,
                'placeholder'   => 'periodic_entry.form.budgets.placeholder',
                'query_builder' => static function (BudgetRepository $budgetRepository): QueryBuilder {
                    $budgetSearchCommand = new BudgetSearchCommand(enabled: true)->setOrderBy('name');

                    return $budgetRepository->getBudgetsQueryBuilder($budgetSearchCommand);
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'         => PeriodicEntry::class,
            'label_format'       => 'periodic_entry.form.%name%.label',
            'translation_domain' => 'forms',
        ]);
    }
}
