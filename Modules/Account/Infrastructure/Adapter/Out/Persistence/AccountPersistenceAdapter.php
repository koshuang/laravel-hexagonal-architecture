<?php

namespace Modules\Account\Infrastructure\Adapter\Out\Persistence;

use Carbon\Carbon;
use Modules\Account\Application\Port\Out\LoadAccountPort;
use Modules\Account\Application\Port\Out\UpdateAccountStatePort;
use Modules\Account\Domain\Entities\Account;
use Modules\Account\Domain\Entities\Activity;
use Modules\Account\Domain\ValueObjects\AccountId;
use Modules\Account\Infrastructure\Adapter\Out\Persistence\Models\AccountModel;
use Modules\Account\Infrastructure\Adapter\Out\Persistence\Models\ActivityModel;

class AccountPersistenceAdapter implements LoadAccountPort, UpdateAccountStatePort
{
    private AccountMapper $accountMapper;

    public function __construct(AccountMapper $accountMapper)
    {
        $this->accountMapper = $accountMapper;
    }

    public function loadAccount(AccountId $accountId, Carbon $baselineDate): Account
    {
        $accountModel = AccountModel::findOrFail($accountId->value);
        $activityModels = ActivityModel::where([
            ['owner_account_id', $accountId->value],
            ['created_at', '>=', $baselineDate->toDateTimeString()],
        ])->get();

        $withdrawalBalance = ActivityModel::where([
            ['owner_account_id', $accountId->value],
            ['source_account_id', $accountId->value],
            ['created_at', '<', $baselineDate->toDateTimeString()],
        ])->sum('amount');

        $depositBalance = ActivityModel::where([
            ['owner_account_id', $accountId->value],
            ['target_account_id', $accountId->value],
            ['created_at', '<', $baselineDate->toDateTimeString()],
        ])->sum('amount');

        return $this->accountMapper->mapToDomainEntity(
            $accountModel,
            $activityModels,
            $withdrawalBalance,
            $depositBalance
        );
    }

    public function updateActivities(Account $account): void
    {
        $account->activityWindow->activities->each(function (Activity $activity) {
            $attributes = collect($this->accountMapper->mapToModel($activity)->toArray())->except(['id']);
            ActivityModel::updateOrCreate(
                $activity->id->isNull() ? [] : ['id' => $activity->id->value],
                $attributes->toArray(),
            );
        });
    }
}
