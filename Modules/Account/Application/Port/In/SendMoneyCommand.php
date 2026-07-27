<?php

declare(strict_types=1);

namespace Modules\Account\Application\Port\In;

use Modules\Account\Domain\ValueObjects\AccountId;
use Modules\Account\Domain\ValueObjects\Money;

class SendMoneyCommand
{
    public function __construct(
        public readonly AccountId $sourceAccountId,
        public readonly AccountId $targetAccountId,
        public readonly Money $money,
    ) {}
}
