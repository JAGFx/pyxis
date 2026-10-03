<?php

namespace App\Domain\PeriodicEntry\Form;

use App\Domain\Account\Entity\Account;
use App\Domain\Account\Message\Query\FindAccounts\FindAccountsQuery;
use App\Domain\Account\Repository\AccountRepository;
use App\Domain\Assignment\Entity\Assignment;
use App\Domain\Assignment\Message\Query\FindAssignments\FindAssignmentsQuery;
use App\Domain\Assignment\Repository\AssignmentRepository;
use App\Domain\Budget\Entity\Budget;
use App\Domain\Budget\Message\Query\FindBudgets\FindBudgetsQuery;
use App\Domain\Budget\Repository\BudgetRepository;
use App\Domain\PeriodicEntry\Message\Command\CreateOrUpdatePeriodicEntry\CreateOrUpdatePeriodicEntryCommand;
use App\Shared\Form\Type\MoneyType;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfonycasts\DynamicForms\DependentField;
use Symfonycasts\DynamicForms\DynamicFormBuilder;

class PeriodicEntryCreateOrUpdateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder = new DynamicFormBuilder($builder);

        $builder
            ->add('account', EntityType::class, [
                'class'         => Account::class,
                'choice_label'  => 'name',
                'query_builder' => function (AccountRepository $repository): QueryBuilder {
                    $searchQuery = new FindAccountsQuery(true)->setOrderBy('name');

                    return $repository->getAccountsQueryBuilder($searchQuery);
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
                    $searchQuery = new FindBudgetsQuery(enabled: true)->setOrderBy('name');

                    return $budgetRepository->getBudgetsQueryBuilder($searchQuery);
                },
            ])
            ->addDependent('assignment', 'account', function (DependentField $field, ?Account $account): void {
                if (is_null($account)) {
                    return;
                }

                $field->add(EntityType::class, [
                    'class'         => Assignment::class,
                    'choice_label'  => 'name',
                    'query_builder' => function (AssignmentRepository $repository) use ($account): QueryBuilder {
                        $searchQuery = new FindAssignmentsQuery($account->getId())
                            ->setOrderBy('name');

                        return $repository->getAssignmentsQueryBuilder($searchQuery);
                    },
                    'required'    => false,
                    'placeholder' => 'periodic_entry.form.assignment.placeholder',
                ]);
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'         => CreateOrUpdatePeriodicEntryCommand::class,
            'label_format'       => 'periodic_entry.form.%name%.label',
            'translation_domain' => 'forms',
        ]);
    }
}
