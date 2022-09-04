<?php

namespace Modules\Account\Domain;

use Carbon\Carbon;
use Modules\Account\Domain\Entities\Activity;
use Modules\Account\Domain\ValueObjects\AccountId;
use Modules\Account\Domain\ValueObjects\ActivityId;
use Modules\Account\Domain\ValueObjects\Money;

class ActivityBuilder
{
    private ActivityId $id;
    private AccountId $ownerAccountId;
    private AccountId $sourceAccountId;
    private AccountId $targetAccountId;
    private Carbon $timestamp;
    private Money $money;

    public function withId(ActivityId $id): ActivityBuilder
    {
        $this->id = $id;

        return $this;
    }

    public function withOwnerAccount(AccountId $ownerAccountId): ActivityBuilder
    {
        $this->ownerAccountId = $ownerAccountId;

        return $this;
    }

    public function withSourceAccount(AccountId $sourceAccountId): ActivityBuilder
    {
        $this->sourceAccountId = $sourceAccountId;

        return $this;
    }

    public function withTargetAccount(AccountId $targetAccountId): ActivityBuilder
    {
        $this->targetAccountId = $targetAccountId;

        return $this;
    }

    public function withTimestamp(Carbon $timestamp): ActivityBuilder
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function withMoney(Money $money): ActivityBuilder
    {
        $this->money = $money;

        return $this;
    }

    public function build(): Activity
    {
        return new Activity(
            $this->id,
            $this->ownerAccountId,
            $this->sourceAccountId,
            $this->targetAccountId,
            $this->timestamp,
            $this->money,
        );
    }
}
