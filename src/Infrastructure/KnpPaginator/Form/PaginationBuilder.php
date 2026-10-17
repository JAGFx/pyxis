<?php

namespace App\Infrastructure\KnpPaginator\Form;

use App\Infrastructure\KnpPaginator\DTO\PaginationInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class PaginationBuilder
{
    public static function buildForm(FormBuilderInterface $builder): void
    {
        $builder
            ->add('page', HiddenType::class, [
                'required' => false,
                'data'     => 1,
                'setter'   => function (PaginationInterface $data, $value): void {
                    // Tampered values (non numeric, negative, overflow) fall back to the first page
                    $page = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                    $data->setPage(false === $page ? 1 : $page);
                },
            ]);
    }
}
