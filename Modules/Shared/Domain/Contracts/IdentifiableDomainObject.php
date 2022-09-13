<?php

namespace Modules\Shared\Domain\Contracts;

/**
 * @template T
 */
abstract class IdentifiableDomainObject implements DomainObject
{
    /**
     * @var T
     */
    public $id;

    public function equals(IdentifiableDomainObject $entity)
    {
        if (! $entity) {
            return false;
        }

        if ($this === $entity) {
            return true;
        }

        return $this->id->equals($entity->id);
    }
}
