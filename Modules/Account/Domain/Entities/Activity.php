<?php

namespace Modules\Account\Domain\Entities;

use Carbon\Carbon;
use Modules\Account\Domain\ValueObjects\AccountId;
use Modules\Account\Domain\ValueObjects\ActivityId;
use Modules\Account\Domain\ValueObjects\Money;
use Modules\Shared\Domain\Contracts\LocalEntity;

/**
 * @extends LocalEntity<ActivityId>
 *
 * @property ActivityId $id
 */
class Activity extends LocalEntity
{
    public function __construct(
        /**
         * @var ActivityId
         */
        public $id,
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
