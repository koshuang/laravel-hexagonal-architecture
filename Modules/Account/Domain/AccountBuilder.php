<?php

namespace Modules\Account\Domain;

use Modules\Account\Domain\Entities\Account;
use Modules\Account\Domain\ValueObjects\AccountId;
use Modules\Account\Domain\ValueObjects\Money;

class AccountBuilder
{
    private AccountId $accountId;
    private Money $baselineBalance;
    private ActivityWindow $activityWindow;

    public function withAccountId(AccountId $accountId): AccountBuilder
    {
        $this->accountId = $accountId;

        return $this;
    }

    public function withBaselineBalance(Money $baselineBalance): AccountBuilder
    {
        $this->baselineBalance = $baselineBalance;

        return $this;
    }

    public function withActivityWindow(ActivityWindow $activityWindow): AccountBuilder
    {
        $this->activityWindow = $activityWindow;

        return $this;
    }

    public function build(): Account
    {
        return Account::withId($this->accountId, $this->baselineBalance, $this->activityWindow);
    }
}
