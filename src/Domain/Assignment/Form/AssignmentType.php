<?php

namespace App\Domain\Assignment\Form;

use App\Domain\Account\DTO\AccountSearchCommand;
use App\Domain\Account\Entity\Account;
use App\Domain\Account\Repository\AccountRepository;
use App\Domain\Assignment\Entity\Assignment;
use Doctrine\ORM\QueryBuilder;
use Override;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssignmentType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('account', EntityType::class, [
                'class'        => Account::class,
                'choice_label' => 'name',
                'label'        => 'Compte',
                'row_attr'     => [
                    'class' => 'form-floating',
                ],
                'query_builder' => function (AccountRepository $repository): QueryBuilder {
                    $accountSearchCommand = new AccountSearchCommand(true)->setOrderBy('name');

                    return $repository->getAccountsQueryBuilder($accountSearchCommand);
                },
            ])
            ->add('name', TextType::class, [
                'label'    => 'Nom',
                'row_attr' => [
                    'class' => 'form-floating',
                ],
            ])
            ->add('amount', MoneyType::class, [
                'label' => 'Valeur',
            ])
        ;
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => Assignment::class,
        ]);
    }
}
