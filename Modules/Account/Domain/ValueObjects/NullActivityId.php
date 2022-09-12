<?php

namespace Modules\Account\Domain\ValueObjects;

class NullActivityId extends ActivityId
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
