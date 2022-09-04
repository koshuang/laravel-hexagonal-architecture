<?php

namespace Modules\Account\Application\Services;

use Modules\Account\Domain\ValueObjects\Money;

class MoneyTransferProperties
{
    public function getMaximumTransferThreshold(): Money
    {
        return Money::of(1000000);
    }
}
