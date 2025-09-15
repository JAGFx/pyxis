<?php

namespace App\Domain\Entry\Form;

use App\Domain\Entry\Request\EntrySearchRequest;
use App\Infrastructure\KnpPaginator\Form\PaginationBuilder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntryPaginationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        PaginationBuilder::buildForm($builder);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', EntrySearchRequest::class);
    }
}
