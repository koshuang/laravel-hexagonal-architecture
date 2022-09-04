<?php

namespace Modules\Account\Application\Services;

use Carbon\Carbon;
use Exception;
use Modules\Account\Application\Port\In\SendMoneyCommand;
use Modules\Account\Application\Port\In\SendMoneyUseCase;
use Modules\Account\Application\Port\Out\AccountLock;
use Modules\Account\Application\Port\Out\LoadAccountPort;
use Modules\Account\Application\Port\Out\UpdateAccountStatePort;

class SendMoneyService implements SendMoneyUseCase
{
    public function __construct(
        private MoneyTransferProperties $moneyTransferProperties,
        private LoadAccountPort $loadAccountPort,
        private UpdateAccountStatePort $updateAccountStatePort,
        private AccountLock $accountLock,
    ) {
    }

    public function sendMoney(SendMoneyCommand $command): bool
    {
        $this->checkThreshold($command);

        $baselineDate = Carbon::now()->addDays(-10);

        $sourceAccount = $this->loadAccountPort->loadAccount(
            $command->sourceAccountId,
            $baselineDate,
        );

        $targetAccount = $this->loadAccountPort->loadAccount(
            $command->targetAccountId,
            $baselineDate,
        );

        if ($sourceAccount->id->isNull()) {
            throw new Exception('expected source account ID not to be empty');
        }

        if ($targetAccount->id->isNull()) {
            throw new Exception('expected target account ID not to be empty');
        }

        $sourceAccountId = $sourceAccount->id;
        $targetAccountId = $targetAccount->id;

        $this->accountLock->lockAccount($sourceAccountId);
        if (! $sourceAccount->withdraw(($command->money), $targetAccountId)) {
            $this->accountLock->releaseAccount($sourceAccountId);

            return false;
        }

        $this->accountLock->lockAccount($targetAccountId);
        if (! $targetAccount->deposit(($command->money), $sourceAccountId)) {
            $this->accountLock->releaseAccount($sourceAccountId);
            $this->accountLock->releaseAccount($targetAccountId);

            return false;
        }

        $this->updateAccountStatePort->updateActivities($sourceAccount);
        $this->updateAccountStatePort->updateActivities($targetAccount);

        $this->accountLock->releaseAccount($sourceAccountId);
        $this->accountLock->releaseAccount($targetAccountId);

        return true;
    }

    private function checkThreshold(SendMoneyCommand $command): void
    {
        if ($command->money->isGreaterThan($this->moneyTransferProperties->getMaximumTransferThreshold())) {
            throw new ThresholdExceededException($this->moneyTransferProperties->getMaximumTransferThreshold(), $command->money);
        }
    }
}
