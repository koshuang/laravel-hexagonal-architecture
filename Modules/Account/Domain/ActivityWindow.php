<?php

namespace Modules\Account\Domain;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Modules\Account\Domain\Entities\Activity;
use Modules\Account\Domain\ValueObjects\AccountId;
use Modules\Account\Domain\ValueObjects\Money;
use Modules\Shared\Domain\Contracts\DomainObject;

class ActivityWindow implements DomainObject
{
    /**
     * The list of account activities within this window.
     *
     * @var Collection<Activity>
     */
    public readonly Collection $activities;

    /**
     * @param  Activity[]  ...$activities
     */
    public function __construct(Activity ...$activities)
    {
        $this->activities = collect($activities);
    }

    public function addActivity(Activity $activity): void
    {
        $this->activities->push($activity);
    }

    /**
     * The timestamp of the first activity within this window.
     */
    public function getStartTimestamp(): Carbon
    {
        return $this->activities
            ->min(fn (Activity $a) => $a->timestamp);
    }

    /**
     * The timestamp of the last activity within this window.
     */
    public function getEndTimestamp(): Carbon
    {
        return $this->activities
            ->max(fn (Activity $a) => $a->timestamp);
    }

    /**
     * Calculates the balance by summing up the values of all activities within this window.
     */
    public function calculateBalance(AccountId $accountId): Money
    {
        /** @var Money $depositBalance */
        $depositBalance = $this->activities
            ->filter(fn (Activity $a) => $a->targetAccountId->equals($accountId))
            ->map(fn (Activity $a) => $a->money)
            ->reduce(fn (Money $a, Money $b) => Money::add($a, $b), Money::ZERO());

        /** @var Money $withdrawalBalance */
        $withdrawalBalance = $this->activities
            ->filter(fn (Activity $a) => $a->sourceAccountId->equals($accountId))
            ->map(fn (Activity $a) => $a->money)
            ->reduce(fn (Money $a, Money $b) => Money::add($a, $b), Money::ZERO());

        return Money::add($depositBalance, $withdrawalBalance->negate());
    }
}
