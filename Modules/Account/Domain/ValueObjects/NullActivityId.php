<?php

namespace Modules\Account\Domain\ValueObjects;

use Override;

class NullActivityId extends ActivityId
{
    public function __construct()
    {
        parent::__construct(-1);
    }

    #[Override]
    public function isNull(): bool
    {
        return true;
    }
}
