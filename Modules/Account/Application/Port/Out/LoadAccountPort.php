<?php

namespace Modules\Account\Application\Port\Out;

use Carbon\Carbon;
use Modules\Account\Domain\Entities\Account;
use Modules\Account\Domain\ValueObjects\AccountId;

interface LoadAccountPort
{
    public function loadAccount(AccountId $accountId, Carbon $baselineDate): Account;
}
