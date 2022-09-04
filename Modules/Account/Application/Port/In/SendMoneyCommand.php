<?php

namespace Modules\Account\Application\Port\In;

use Modules\Account\Domain\ValueObjects\AccountId;
use Modules\Account\Domain\ValueObjects\Money;
use Spatie\DataTransferObject\DataTransferObject;

class SendMoneyCommand extends DataTransferObject
{
    public AccountId $sourceAccountId;
    public AccountId $targetAccountId;
    public Money $money;
}
