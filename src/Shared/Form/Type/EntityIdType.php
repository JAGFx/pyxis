<?php

namespace App\Shared\Form\Type;

use App\Shared\Entity\IntIdentifierInterface;
use App\Shared\Form\DataTransformer\EntityToIdTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Override;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @template T of IntIdentifierInterface
 */
class EntityIdType extends AbstractType
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var class-string<T> $entityClass */
        $entityClass = $options['class'];

        $builder->addModelTransformer(new EntityToIdTransformer(
            $this->entityManager,
            $entityClass,
        ));
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'invalid_message' => 'The selected choice is invalid.',
        ]);
    }

    public function getParent(): string
    {
        return EntityType::class;
    }
}
