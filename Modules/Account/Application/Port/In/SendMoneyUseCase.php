<?php

namespace Modules\Account\Application\Port\In;

interface SendMoneyUseCase
{
    public function sendMoney(SendMoneyCommand $command): bool;
}
