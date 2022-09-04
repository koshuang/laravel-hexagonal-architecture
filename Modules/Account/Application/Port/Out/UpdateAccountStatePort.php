<?php

namespace Modules\Account\Application\Port\Out;

use Modules\Account\Domain\Entities\Account;

interface UpdateAccountStatePort
{
    public function updateActivities(Account $account): void;
}
