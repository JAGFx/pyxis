<?php

declare(strict_types=1);

namespace App\Domain\PeriodicEntry\Form;

use App\Domain\Entry\Entity\EntryTypeEnum;
use App\Domain\PeriodicEntry\Message\Query\FindPeriodicEntriesQuery;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PeriodicEntrySearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => false,
            ])
            ->add('entry_type_enum', EnumType::class, [
                'property_path' => 'entryTypeEnum',
                'class'         => EntryTypeEnum::class,
                'required'      => false,
                'placeholder'   => 'shared.default.placeholders.all',
                'choice_label'  => 'label',
                'expanded'      => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'         => FindPeriodicEntriesQuery::class,
            'label_format'       => 'periodic_entry.search.%name%.label',
            'translation_domain' => 'forms',
        ]);
    }
}
