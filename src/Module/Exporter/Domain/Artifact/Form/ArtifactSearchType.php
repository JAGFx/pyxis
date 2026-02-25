<?php

namespace App\Module\Exporter\Domain\Artifact\Form;

use App\Infrastructure\KnpPaginator\Form\PaginationBuilder;
use App\Module\Exporter\Domain\Artifact\Entity\ArtifactStatusEnum;
use App\Module\Exporter\Domain\Artifact\Message\Query\FindArtifacts\FindArtifactsQuery;
use App\Module\Exporter\Infrastructure\Document\Model\DocumentTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArtifactSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        PaginationBuilder::buildForm($builder);

        $builder
            ->add('status', EnumType::class, [
                'class'                     => ArtifactStatusEnum::class,
                'required'                  => false,
                'placeholder'               => 'shared.default.placeholders.all',
                'choice_label'              => 'label',
                'choice_translation_domain' => 'messages',
            ])
            ->add('documentType', EnumType::class, [
                'class'                     => DocumentTypeEnum::class,
                'required'                  => false,
                'placeholder'               => 'shared.default.placeholders.all',
                'choice_label'              => 'label',
                'choice_translation_domain' => 'messages',
            ])
            ->add('startDate', DateType::class, [
                'required' => false,
                'widget'   => 'single_text',
                'input'    => 'datetime_immutable',
            ])
            ->add('endDate', DateType::class, [
                'required' => false,
                'widget'   => 'single_text',
                'input'    => 'datetime_immutable',
            ])
        ;
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
