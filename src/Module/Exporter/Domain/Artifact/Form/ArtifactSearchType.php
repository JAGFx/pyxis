<?php

namespace App\Module\Exporter\Domain\Artifact\Form;

use App\Infrastructure\KnpPaginator\Form\PaginationBuilder;
use App\Module\Exporter\Domain\Artifact\Message\Query\FindArtifacts\FindArtifactsQuery;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArtifactSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        PaginationBuilder::buildForm($builder);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'         => FindArtifactsQuery::class,
            'label_format'       => 'exporter.artifact.search.%name%.label',
            'translation_domain' => 'forms',
        ]);
    }
}
