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
                    /** @var int $page */
                    $page = max(1, $value ?? 1);
                    $data->setPage($page);
                },
            ]);
    }
}
