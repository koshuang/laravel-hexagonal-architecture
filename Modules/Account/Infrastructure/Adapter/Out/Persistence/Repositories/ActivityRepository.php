<?php

namespace Modules\Account\Infrastructure\Adapter\Out\Persistence\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Modules\Account\Infrastructure\Adapter\Out\Persistence\Models\ActivityModel;

class ActivityRepository
{
    /**
     * @return Collection<ActivityModel>
     */
    public function findByOwnerSince(int|string $ownerAccountId, Carbon $since): Collection
    {
        return ActivityModel::where([
            ['owner_account_id', $ownerAccountId],
            ['created_at', '>=', $since->toDateTimeString()],
        ])->get();
    }

    public function getDepositBalanceUntil(int|string $accountId, Carbon $until): int
    {
        return ActivityModel::where([
            ['owner_account_id', $accountId],
            ['source_account_id', $accountId],
            ['created_at', '<', $until->toDateTimeString()],
        ])->sum('amount');
    }

    public function getWithdrawalBalanceUntil(int|string $accountId, Carbon $until): int
    {
        return ActivityModel::where([
            ['owner_account_id', $accountId],
            ['target_account_id', $accountId],
            ['created_at', '<', $until->toDateTimeString()],
        ])->sum('amount');
    }
}
