<?php

namespace Modules\Account\Application\Port\Out;

use Modules\Account\Domain\ValueObjects\AccountId;

interface AccountLock
{
    public function lockAccount(AccountId $accountId): void;

    public function releaseAccount(AccountId $accountId): void;
}
