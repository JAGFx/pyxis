<?php

namespace App\Domain\Entry\Form;

use App\Domain\Account\Entity\Account;
use App\Domain\Account\Model\AccountSearchCommand;
use App\Domain\Account\Repository\AccountRepository;
use App\Domain\Budget\Entity\Budget;
use App\Domain\Entry\Entity\Entry;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntryType extends AbstractType
{
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
                    $accountSearchCommand = new AccountSearchCommand(true);

                    return $repository->getAccountsQueryBuilder($accountSearchCommand);
                },
            ])
            ->add('name', TextType::class, [
                'label'    => 'Intitulé',
                'row_attr' => [
                    'class' => 'form-floating',
                ],
            ])
            ->add('amount', MoneyType::class, [
                'label' => 'Valeur',
            ])
            ->add('budget', EntityType::class, [
                'class'        => Budget::class,
                'choice_label' => 'name',
                'required'     => false,
                'placeholder'  => '-- Pas de budget --',
                'row_attr'     => [
                    'class' => 'form-floating',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Entry::class);
    }
}
