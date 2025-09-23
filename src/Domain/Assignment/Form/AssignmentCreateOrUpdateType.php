<?php

namespace App\Domain\Assignment\Form;

use App\Domain\Account\Entity\Account;
use App\Domain\Account\Message\Query\FindAccounts\FindAccountsQuery;
use App\Domain\Account\Repository\AccountRepository;
use App\Domain\Assignment\Message\Command\CreateOrUpdateAssignmentCommand;
use App\Shared\Form\Type\MoneyType;
use Doctrine\ORM\QueryBuilder;
use Override;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssignmentCreateOrUpdateType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
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
        ;
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'         => CreateOrUpdateAssignmentCommand::class,
            'label_format'       => 'assignment.form.%name%.label',
            'translation_domain' => 'forms',
        ]);
    }
}
