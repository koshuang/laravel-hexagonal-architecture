<?php

namespace Modules\Account\Tests\Unit\Domain\Entities;

use Modules\Account\Domain\ActivityWindow;
use Modules\Account\Domain\ValueObjects\AccountId;
use Modules\Account\Domain\ValueObjects\Money;
use Modules\Account\Tests\Common\AccountTestData;
use Modules\Account\Tests\Common\ActivityTestData;
use Tests\TestCase;

class AccountTest extends TestCase
{
    /**
     * @test
     */
    public function calculatesBalance(): void
    {
        $accountId = new AccountId(1);
        $account = AccountTestData::defaultAccount()
            ->withAccountId($accountId)
            ->withBaselineBalance(Money::of(555))
            ->withActivityWindow(new ActivityWindow(
                ActivityTestData::defaultActivity()
                    ->withTargetAccount($accountId)
                    ->withMoney(Money::of(999))
                    ->build(),
                ActivityTestData::defaultActivity()
                    ->withTargetAccount($accountId)
                    ->withMoney(Money::of(1))
                    ->build(),
            ))
            ->build();

        $balance = $account->calculateBalance();

        $this->assertEquals(Money::of(1555), $balance);
    }
}