<?php

declare(strict_types=1);

namespace App\Domain\Account\Form;

use App\Domain\Account\Entity\Account;
use App\Shared\Form\Type\YesNoType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('enable', YesNoType::class, [
                'required'    => false,
                'placeholder' => 'shared.default.placeholders.all',
            ])
            ->add('name', TextType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'         => Account::class,
            'label_format'       => 'account.search.%name%.label',
            'translation_domain' => 'forms',
        ]);
    }
}
