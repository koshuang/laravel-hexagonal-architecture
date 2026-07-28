<?php

namespace Modules\Account\Domain\Entities;

use Carbon\Carbon;
use Modules\Account\Domain\ActivityWindow;
use Modules\Account\Domain\ValueObjects\AccountId;
use Modules\Account\Domain\ValueObjects\Money;
use Modules\Account\Domain\ValueObjects\NullAccountId;
use Modules\Account\Domain\ValueObjects\NullActivityId;
use Modules\Shared\Domain\Contracts\AggregateRoot;

/**
 * @extends AggregateRoot<AccountId>
 *
 * @property AccountId $id
 */
class Account extends AggregateRoot
{
    public function __construct(
        /**
         * The unique ID of the account.
         *
         * @var AccountId
         */
        public $id,
        /**
         * The baseline balance of the account. This was the balance of the account before the first
         * activity in the activityWindow.
         */
        public readonly Money $baselineBalance,
        /**
         * The window of latest activities on this account.
         */
        public readonly ActivityWindow $activityWindow,
    ) {
    }

    /**
     * Creates an {@link Account} entity without an ID. Use to create a new entity that is not yet
     * persisted.
     */
    public static function withoutId(AccountId $id, Money $baselineBalance, ActivityWindow $activityWindow): Account
    {
        return new Account(new NullAccountId(), $baselineBalance, $activityWindow);
    }

    /**
     * Creates an {@link Activity} entity with an ID. Use to reconstitute a persisted entity.
     */
    public static function withId(AccountId $id, Money $baselineBalance, ActivityWindow $activityWindow): Account
    {
        return new Account($id, $baselineBalance, $activityWindow);
    }

    /**
     * Calculates the total balance of the account by adding the activity values to the baseline balance.
     */
    public function calculateBalance(): Money
    {
        return Money::add(
            $this->baselineBalance,
            $this->activityWindow->calculateBalance($this->id),
        );
    }

    /**
     * Tries to withdraw a certain amount of money from this account.
     * If successful, creates a new activity with a negative value.
     * return true if the withdrawal was successful, false if not.
     */
    public function withdraw(Money $money, AccountId $targetAccountId): bool
    {
        if (! $this->mayWithdraw($money)) {
            return false;
        }

        $withdrawal = new Activity(
            new NullActivityId(),
            $this->id,
            $this->id,
            $targetAccountId,
            Carbon::now(),
            $money,
        );

        $this->activityWindow->addActivity($withdrawal);

        return true;
    }

    /**
     * Tries to deposit a certain amount of money to this account.
     * If successful, creates a new activity with a positive value.
     * return true if the deposit was successful, false if not.
     */
    public function deposit(Money $money, AccountId $sourceAccountId): bool
    {
        $deposit = new Activity(
            new NullActivityId(),
            $this->id,
            $sourceAccountId,
            $this->id,
            Carbon::now(),
            $money,
        );

        $this->activityWindow->addActivity($deposit);

        return true;
    }

    private function mayWithdraw(Money $money): bool
    {
        return Money::add(
            $this->calculateBalance(),
            $money->negate(),
        )->isPositiveOrZero();
    }
}
