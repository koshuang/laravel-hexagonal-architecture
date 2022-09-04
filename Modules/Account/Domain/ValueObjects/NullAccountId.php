<?php

namespace Modules\Account\Domain\ValueObjects;

class NullAccountId extends AccountId
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
