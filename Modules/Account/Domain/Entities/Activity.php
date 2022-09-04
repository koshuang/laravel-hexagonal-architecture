<?php

namespace Modules\Account\Domain\Entities;

use Carbon\Carbon;
use Modules\Account\Domain\ValueObjects\AccountId;
use Modules\Account\Domain\ValueObjects\ActivityId;
use Modules\Account\Domain\ValueObjects\Money;

class Activity
{
    public function __construct(

        public readonly ActivityId $id,
        /**
         * The account that owns this activity.
         */
        public readonly AccountId $ownerAccountId,

        /**
         * The debited account.
         */
        public readonly AccountId $sourceAccountId,

        /**
         * The credited account.
         */
        public readonly AccountId $targetAccountId,

        /**
         * The timestamp of the activity.
         */
        public readonly Carbon $timestamp,

        /**
         * The money that was transferred between the accounts.
         */
        public readonly Money $money,
    ) {
    }
}
