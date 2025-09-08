<?php

declare(strict_types=1);

namespace App\Domain\Account\Form;

use App\Domain\Account\Entity\Account;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('enable', ChoiceType::class, [ // TODO: Factorise and create a type like YesNoChoiceType + translation
                'choices'  => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'required' => false,
                'label'    => 'Actif',
                'expanded' => true,
            ])
            ->add('name', TextType::class, [
                'required' => false,
                'label'    => 'Nom',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', AccountSearchType::class);
    }
}
