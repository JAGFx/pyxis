<?php

namespace App\Domain\Entry\Form;

use App\Domain\Account\Entity\Account;
use App\Domain\Account\Message\Query\FindAccounts\FindAccountsQuery;
use App\Domain\Account\Repository\AccountRepository;
use App\Domain\Assignment\Entity\Assignment;
use App\Domain\Assignment\Message\Query\FindAssignments\FindAssignmentsQuery;
use App\Domain\Assignment\Repository\AssignmentRepository;
use App\Domain\Budget\Entity\Budget;
use App\Domain\Budget\Message\Query\FindBudgets\FindBudgetsQuery;
use App\Domain\Budget\Repository\BudgetRepository;
use App\Domain\Entry\Message\Command\CreateOrUpdateEntry\CreateOrUpdateEntryCommand;
use App\Shared\Form\Type\MoneyType;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfonycasts\DynamicForms\DependentField;
use Symfonycasts\DynamicForms\DynamicFormBuilder;

class EntryCreateOrUpdateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder = new DynamicFormBuilder($builder);

        /** @var ?CreateOrUpdateEntryCommand $command */
        $command = $builder->getData();

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
            ->add('amount', MoneyType::class)
            ->add('budget', EntityType::class, [
                'class'         => Budget::class,
                'choice_label'  => 'name',
                'query_builder' => function (BudgetRepository $repository): QueryBuilder {
                    $searchQuery = new FindBudgetsQuery(enabled: true)->setOrderBy('name');

                    return $repository->getBudgetsQueryBuilder($searchQuery);
                },
                'required'    => false,
                'placeholder' => 'entry.form.budget.placeholder',
            ])
            ->addDependent('assignment', 'account', function (DependentField $field, ?Account $account) use ($command): void {
                if (is_null($account) || !is_null($command?->getOriginId())) {
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
                    'placeholder' => 'entry.form.assignment.placeholder',
                ]);
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'         => CreateOrUpdateEntryCommand::class,
            'label_format'       => 'entry.form.%name%.label',
            'translation_domain' => 'forms',
        ]);
    }
}
