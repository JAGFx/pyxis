<?php

namespace App\Infrastructure\Doctrine\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use LogicException;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class EntityFinder
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $entityClass
     *
     * @return T|null
     */
    public function findByIntIdentifier(string $entityClass, ?int $id): ?object
    {
        if (is_null($id)) {
            throw new LogicException('An identifier is required. Null given.');
        }

        /** @var EntityRepository<T> $repository */
        $repository = $this->entityManager->getRepository($entityClass);

        /** @var T|null $entity */
        $entity = $repository->find($id);

        return $entity;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $entityClass
     *
     * @return T
     *
     * @throws ReflectionException
     * @throws NotFoundHttpException
     */
    public function findByIntIdentifierOrFail(string $entityClass, ?int $id): object
    {
        /** @var T|null $entity */
        $entity = $this->findByIntIdentifier($entityClass, $id);

        if (is_null($entity)) {
            throw new NotFoundHttpException(sprintf('%s not found with id %s', new ReflectionClass($entityClass)->getShortName(), $id));
        }

        return $entity;
    }
}
