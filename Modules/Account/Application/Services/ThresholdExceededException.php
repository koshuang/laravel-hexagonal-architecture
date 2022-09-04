<?php

namespace Modules\Account\Application\Services;

use Modules\Account\Domain\ValueObjects\Money;
use RuntimeException;

class ThresholdExceededException extends RuntimeException
{
    public function __construct(Money $threshold, Money $actual)
    {
        parent::__construct("Maximum threshold for transferring money exceeded: tried to transfer {$actual} but threshold is {$threshold}!");
    }
}
