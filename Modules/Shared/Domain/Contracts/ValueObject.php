<?php

namespace Modules\Shared\Domain\Contracts;

/**
 * @template T
 */
abstract class ValueObject implements DomainObject
{
    /**
     * @param  T  $vo
     */
    public function equals($vo): bool
    {
        if (! $vo) {
            return false;
        }

        return json_encode($this) === json_encode($vo);
    }
}
