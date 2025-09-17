<?php

namespace App\Shared\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;

class MoneyType extends \Symfony\Component\Form\Extension\Core\Type\MoneyType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'html5' => true,
            'attr'  => [
                'step' => 0.01,
            ],
        ]);
    }
}
