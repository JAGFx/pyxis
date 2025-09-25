<?php

namespace App\Infrastructure\Cqs\Form\DataTransformer;

use App\Shared\Entity\IntIdentifierInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @template T of IntIdentifierInterface
 *
 * @implements DataTransformerInterface<T|mixed|null, int|string|null>
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
     * Transform ID to Entity (for initial data display in form)
     *
     * @param int|string|null $value
     *
     * @return IntIdentifierInterface|null
     */
    public function transform(mixed $value): ?object
    {
        if (null === $value) {
            return null;
        }

        // Convert string to int if needed
        if (is_string($value) && is_numeric($value)) {
            $value = (int) $value;
        }

        if (!is_int($value)) {
            throw new TransformationFailedException('Expected an integer or numeric string');
        }

        /** @var IntIdentifierInterface|null $entity */
        $entity = $this->entityManager
            ->getRepository($this->entityClass)
            ->find($value);

        if (null === $entity) {
            throw new TransformationFailedException(sprintf('Entity %s with ID %d not found', $this->entityClass, $value));
        }

        return $entity;
    }

    /**
     * Transform Entity to ID (for form submission to command)
     *
     * @param IntIdentifierInterface|mixed|null $value
     */
    public function reverseTransform(mixed $value): ?int
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof IntIdentifierInterface) {
            throw new TransformationFailedException('Expected an instance of ' . IntIdentifierInterface::class);
        }

        return $value->getId();
    }
}
