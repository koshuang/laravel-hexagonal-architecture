<?php

namespace Modules\Account\Tests\Unit\Domain\Entities;

use Carbon\Carbon;
use Modules\Account\Domain\ActivityWindow;
use Modules\Account\Domain\ValueObjects\AccountId;
use Modules\Account\Domain\ValueObjects\Money;
use Modules\Account\Tests\Common\ActivityTestData;
use Tests\TestCase;

class ActivityWindowTest extends TestCase
{
    /**
     * @test
     */
    public function calculatesStartTimestamp(): void
    {
        $window = new ActivityWindow(
            ActivityTestData::defaultActivity()->withTimestamp($this->startDate())->build(),
            ActivityTestData::defaultActivity()->withTimestamp($this->inBetweenDate())->build(),
            ActivityTestData::defaultActivity()->withTimestamp($this->endDate())->build(),
        );

        $this->assertEquals($this->startDate(), $window->getStartTimestamp());
    }

    /**
     * @test
     */
    public function calculatesEndTimestamp(): void
    {
        $window = new ActivityWindow(
            ActivityTestData::defaultActivity()->withTimestamp($this->startDate())->build(),
            ActivityTestData::defaultActivity()->withTimestamp($this->inBetweenDate())->build(),
            ActivityTestData::defaultActivity()->withTimestamp($this->endDate())->build(),
        );

        $this->assertEquals($this->endDate(), $window->getEndTimestamp());
    }

    /**
     * @test
     */
    public function calculatesBalance(): void
    {
        $account1 = new AccountId(1);
        $account2 = new AccountId(2);

        $window = new ActivityWindow(
            ActivityTestData::defaultActivity()
                ->withSourceAccount($account1)
                ->withTargetAccount($account2)
                ->withMoney(Money::of(999))
                ->build(),
            ActivityTestData::defaultActivity()
                ->withSourceAccount($account1)
                ->withTargetAccount($account2)
                ->withMoney(Money::of(1))
                ->build(),
            ActivityTestData::defaultActivity()
                ->withSourceAccount($account2)
                ->withTargetAccount($account1)
                ->withMoney(Money::of(500))
                ->build(),
        );

        $this->assertEquals(Money::of(-500), $window->calculateBalance($account1));
        $this->assertEquals(Money::of(500), $window->calculateBalance($account2));
    }

    private function startDate(): Carbon
    {
        return Carbon::parse('2022-08-03');
    }

    private function inBetweenDate(): Carbon
    {
        return Carbon::parse('2022-08-04');
    }

    private function endDate(): Carbon
    {
        return Carbon::parse('2022-08-05');
    }
}
