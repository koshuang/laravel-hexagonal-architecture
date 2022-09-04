<?php

namespace Modules\Account\Tests\Common;

use Modules\Account\Domain\AccountBuilder;
use Modules\Account\Domain\ActivityWindow;
use Modules\Account\Domain\ValueObjects\AccountId;
use Modules\Account\Domain\ValueObjects\Money;

class AccountTestData
{
    public static function defaultAccount(): AccountBuilder
    {
        return (new AccountBuilder())
            ->withAccountId(new AccountId(42))
            ->withBaselineBalance(Money::of(999))
            ->withActivityWindow(new ActivityWindow(
                ActivityTestData::defaultActivity()->build(),
                ActivityTestData::defaultActivity()->build(),
            ));
    }
}
