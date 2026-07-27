<?php

namespace Modules\Account\Application\Services;

use Carbon\Carbon;
use Exception;
use Modules\Account\Application\Port\In\SendMoneyCommand;
use Modules\Account\Application\Port\In\SendMoneyUseCase;
use Modules\Account\Application\Port\Out\AccountLock;
use Modules\Account\Application\Port\Out\LoadAccountPort;
use Modules\Account\Application\Port\Out\UpdateAccountStatePort;
use Modules\Account\Domain\Entities\Account;

class SendMoneyService implements SendMoneyUseCase
{
    public function __construct(
        private readonly MoneyTransferProperties $moneyTransferProperties,
        private readonly LoadAccountPort $loadAccountPort,
        private readonly UpdateAccountStatePort $updateAccountStatePort,
        private readonly AccountLock $accountLock,
    ) {
    }

    public function sendMoney(SendMoneyCommand $command): bool
    {
        $this->checkThreshold($command);

        $sourceAccount = $this->loadSourceAccount($command);
        $targetAccount = $this->loadTargetAccount($command);

        $this->validateAccounts($sourceAccount, $targetAccount);

        return $this->processTransfer($sourceAccount, $targetAccount, $command);
    }

    private function loadSourceAccount(SendMoneyCommand $command): Account
    {
        $baselineDate = Carbon::now()->addDays(-10);

        return $this->loadAccountPort->loadAccount(
            $command->sourceAccountId,
            $baselineDate,
        );
    }

    private function loadTargetAccount(SendMoneyCommand $command): Account
    {
        $baselineDate = Carbon::now()->addDays(-10);

        return $this->loadAccountPort->loadAccount(
            $command->targetAccountId,
            $baselineDate,
        );
    }

    private function validateAccounts(Account $sourceAccount, Account $targetAccount): void
    {
        if ($sourceAccount->id->isNull()) {
            throw new Exception('expected source account ID not to be empty');
        }

        if ($targetAccount->id->isNull()) {
            throw new Exception('expected target account ID not to be empty');
        }
    }

    private function processTransfer(Account $sourceAccount, Account $targetAccount, SendMoneyCommand $command): bool
    {
        $sourceAccountId = $sourceAccount->id;
        $targetAccountId = $targetAccount->id;

        $this->accountLock->lockAccount($sourceAccountId);
        if (! $sourceAccount->withdraw($command->money, $targetAccountId)) {
            $this->accountLock->releaseAccount($sourceAccountId);

            return false;
        }

        $this->accountLock->lockAccount($targetAccountId);
        if (! $targetAccount->deposit($command->money, $sourceAccountId)) {
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
            throw new ThresholdExceeded($this->moneyTransferProperties->getMaximumTransferThreshold(), $command->money);
        }
    }
}
