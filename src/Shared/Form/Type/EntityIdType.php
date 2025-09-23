<?php

namespace App\Shared\Form\Type;

use App\Shared\Entity\EntityIntIdentifierInterface;
use App\Shared\Form\DataTransformer\EntityToIdTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Override;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class EntityIdType extends AbstractType
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var class-string<EntityIntIdentifierInterface> $entityClass */
        $entityClass = $options['class'];

        $builder->addModelTransformer(new EntityToIdTransformer(
            $this->entityManager,
            $entityClass,
        ));
    }

    public function getParent(): string
    {
        return EntityType::class;
    }
}
