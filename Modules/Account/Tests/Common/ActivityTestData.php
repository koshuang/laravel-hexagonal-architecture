<?php

namespace Modules\Account\Tests\Common;

use Carbon\Carbon;
use Modules\Account\Domain\ActivityBuilder;
use Modules\Account\Domain\ValueObjects\AccountId;
use Modules\Account\Domain\ValueObjects\Money;
use Modules\Account\Domain\ValueObjects\NullActivityId;

class ActivityTestData
{
    public static function defaultActivity(): ActivityBuilder
    {
        return (new ActivityBuilder())
            ->withId(new NullActivityId)
            ->withOwnerAccount(new AccountId(42))
            ->withSourceAccount(new AccountId(42))
            ->withTargetAccount(new AccountId(41))
            ->withTimestamp(Carbon::now())
            ->withMoney(Money::of(999));
    }
}
