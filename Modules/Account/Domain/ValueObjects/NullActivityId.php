<?php

namespace Modules\Account\Domain\ValueObjects;

class NullActivityId extends ActivityId
{
    public function __construct()
    {
        $this->value = -1;
    }

    public function isNull(): bool
    {
        return true;
    }
}
