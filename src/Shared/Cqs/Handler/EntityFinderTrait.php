<?php

namespace App\Shared\Cqs\Handler;

use LogicException;
use ReflectionClass;
use ReflectionException;

trait EntityFinderTrait
{
    /**
     * @template T of object
     *
     * @param class-string<T> $entityClass
     *
     * @return T
     *
     * @throws ReflectionException
     */
    private function findEntityByIntIdentifierOrFail(string $entityClass, ?int $id): object
    {
        if (is_null($id)) {
            throw new LogicException('Identifier is null');
        }

        $entity = $this->entityManager
            ->getRepository($entityClass)
            ->find($id);

        if (is_null($entity)) {
            throw new LogicException(sprintf('%s not found with id %s', new ReflectionClass($entityClass)->getShortName(), $id));
        }

        return $entity;
    }
}
