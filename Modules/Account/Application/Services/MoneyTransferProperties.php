<?php

namespace Modules\Account\Application\Services;

use Modules\Account\Domain\ValueObjects\Money;

class MoneyTransferProperties
{
    public function __construct(
        private readonly int $maximumTransferThreshold = 1000000,
    ) {
    }

    public function getMaximumTransferThreshold(): Money
    {
        return Money::of($this->maximumTransferThreshold);
    }
}
