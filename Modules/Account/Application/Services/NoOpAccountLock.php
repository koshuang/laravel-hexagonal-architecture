<?php

namespace Modules\Account\Application\Services;

use Modules\Account\Application\Port\Out\AccountLock;
use Modules\Account\Domain\ValueObjects\AccountId;

class NoOpAccountLock implements AccountLock
{
    public function lockAccount(AccountId $accountId): void
    {
    }

    public function releaseAccount(AccountId $accountId): void
    {
    }
}
