<?php

namespace App\Shared\Entity;

use Doctrine\Common\Collections\Collection;

trait CollectionManagerTrait
{
    /**
     * @template T of object
     *
     * @param Collection<int, T> $collection
     * @param T                  $item
     */
    protected function addToCollection(
        Collection $collection,
        object $item,
        string $setterMethod,
        mixed $ownerValue = null,
    ): void {
        if (!$collection->contains($item)) {
            $collection->add($item);
            if ($setterMethod && method_exists($item, $setterMethod)) {
                $item->$setterMethod($ownerValue ?? $this);
            }
        }
    }

    /**
     * @template T of object
     *
     * @param Collection<int, T> $collection
     * @param T                  $item
     */
    protected function removeFromCollection(
        Collection $collection,
        object $item,
        string $setterMethod,
        mixed $nullValue = null,
    ): void {
        if ($collection->removeElement($item) && ($setterMethod && method_exists($item, $setterMethod))) {
            $item->$setterMethod($nullValue);
        }
    }
}
