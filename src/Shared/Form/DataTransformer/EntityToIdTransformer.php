<?php

namespace App\Shared\Form\DataTransformer;

use App\Shared\Entity\EntityIntIdentifierInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @implements DataTransformerInterface<int|null, object|null>
 *
 * @template T of EntityIntIdentifierInterface
 */
readonly class EntityToIdTransformer implements DataTransformerInterface
{
    /**
     * @param class-string<T> $entityClass
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private string $entityClass,
    ) {
    }

    public function transform(mixed $value): ?object
    {
        if (null === $value) {
            return null;
        }

        return $this->entityManager
            ->getRepository($this->entityClass)
            ->find($value);
    }

    public function reverseTransform(mixed $value): ?int
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof EntityIntIdentifierInterface) {
            throw new TransformationFailedException('Expected an instance of ' . EntityIntIdentifierInterface::class);
        }

        return $value->getId();
    }
}
