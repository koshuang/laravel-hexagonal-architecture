<?php

namespace Modules\Account\Domain\Entities;

use Modules\Account\Domain\ActivityWindow;
use Modules\Account\Domain\ValueObjects\AccountId;
use Modules\Account\Domain\ValueObjects\Money;
use Modules\Account\Domain\ValueObjects\NullAccountId;

class Account
{
    public function __construct(
        /**
         * The unique ID of the account.
         */
        public readonly AccountId $id,

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
            $this->activityWindow->calculateBalance($this->id)
        );
    }
}
