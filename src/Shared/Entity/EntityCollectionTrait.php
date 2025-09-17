<?php

namespace App\Shared\Entity;

use Doctrine\Common\Collections\Collection;

trait EntityCollectionTrait
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
        ?string $setterMethod = null,
        mixed $ownerValue = null,
    ): void {
        if (!$collection->contains($item)) {
            $collection->add($item);
            if (!is_null($setterMethod) && method_exists($item, $setterMethod)) {
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
        ?string $setterMethod = null,
        mixed $nullValue = null,
    ): void {
        if ($collection->removeElement($item) && (!is_null($setterMethod) && method_exists($item, $setterMethod))) {
            $item->$setterMethod($nullValue);
        }
    }
}
