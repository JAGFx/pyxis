<?php

namespace App\Domain\Budget\Form;

use App\Domain\Budget\Message\Command\CreateOrUpdateBudgetCommand;
use App\Shared\Form\Type\MoneyType;
use App\Shared\Form\Type\YesNoType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BudgetCreateOrUpdateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('amount', MoneyType::class)
            ->add('enabled', YesNoType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'         => CreateOrUpdateBudgetCommand::class,
            'label_format'       => 'budget.form.%name%.label',
            'translation_domain' => 'forms',
        ]);
    }
}
