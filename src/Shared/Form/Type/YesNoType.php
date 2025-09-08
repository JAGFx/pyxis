<?php

declare(strict_types=1);

namespace App\Shared\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class YesNoType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => [
                'shared.yes_no.options.yes' => true,
                'shared.yes_no.options.no'  => false,
            ],
            'expanded'           => true,
            'translation_domain' => 'forms',
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
