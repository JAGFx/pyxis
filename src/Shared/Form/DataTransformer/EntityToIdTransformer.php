<?php

namespace App\Shared\Form\DataTransformer;

use App\Shared\Entity\IntIdentifierInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @template T of object
 *
 * @implements DataTransformerInterface<T|null, mixed|null>
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

    /**
     * @param T|null $value
     */
    public function transform(mixed $value): ?int
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof IntIdentifierInterface) {
            throw new TransformationFailedException('Expected an instance of ' . IntIdentifierInterface::class);
        }

        return $value->getId();
    }

    /**
     * @return T|null
     */
    public function reverseTransform(mixed $value): ?object
    {
        if (null === $value) {
            return null;
        }

        if (!is_int($value)) {
            throw new TransformationFailedException('Expected an integer');
        }

        /** @var T|null $entity */
        $entity = $this->entityManager
            ->getRepository($this->entityClass)
            ->find($value);

        if (null === $entity) {
            throw new TransformationFailedException(sprintf('Entity %s with ID %d not found', $this->entityClass, $value));
        }

        return $entity;
    }
}
