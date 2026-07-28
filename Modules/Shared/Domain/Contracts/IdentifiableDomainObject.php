<?php

namespace Modules\Shared\Domain\Contracts;

/**
 * @template T of Identity
 */
abstract class IdentifiableDomainObject implements DomainObject
{
    /**
     * @var T
     */
    public $id;

    /**
     * @param IdentifiableDomainObject<T> $entity
     */
    public function equals(self $entity): bool
    {
        if ($this === $entity) {
            return true;
        }

        return $this->id->equals($entity->id);
    }
}
