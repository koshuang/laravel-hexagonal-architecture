<?php

namespace Modules\Account\Domain\ValueObjects;

class NullAccountId extends AccountId
{
    public function __construct()
    {
        parent::__construct(-1);
    }

    public function isNull(): bool
    {
        return true;
    }
}
