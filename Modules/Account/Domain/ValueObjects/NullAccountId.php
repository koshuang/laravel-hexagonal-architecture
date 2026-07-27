<?php

namespace Modules\Account\Domain\ValueObjects;

use Override;

class NullAccountId extends AccountId
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
