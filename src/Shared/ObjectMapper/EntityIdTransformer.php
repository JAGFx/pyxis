<?php

namespace App\Shared\ObjectMapper;

use App\Domain\Account\Entity\Account;
use App\Infrastructure\Doctrine\Service\EntityFinder;
use Symfony\Component\ObjectMapper\TransformCallableInterface;

readonly class EntityIdTransformer implements TransformCallableInterface
{
    public function __construct(
        private EntityFinder $entityFinder,
    ) {
    }

    public function __invoke(mixed $value, object $source, ?object $target): mixed
    {
        // Source = Command
        // Target = Entity

        /*
         * FIXME: Waiting to a way to get property context
         * @see https://github.com/symfony/symfony/issues/61357
         */

        return $this->entityFinder->findByIntIdentifierOrFail(Account::class, $value);
    }
}
