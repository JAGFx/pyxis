<?php

namespace App\Infrastructure\KnpPaginator\Form;

use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;

class PaginationBuilder
{
    public static function buildForm(FormBuilderInterface $builder): void
    {
        $builder
            ->add('page', NumberType::class, [
                'required' => false,
            ]);
    }
}
