<?php

namespace Modules\Account\Infrastructure\Adapter\Out\Persistence;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Modules\Account\Domain\ActivityWindow;
use Modules\Account\Domain\Entities\Account;
use Modules\Account\Domain\Entities\Activity;
use Modules\Account\Domain\ValueObjects\AccountId;
use Modules\Account\Domain\ValueObjects\ActivityId;
use Modules\Account\Domain\ValueObjects\Money;
use Modules\Account\Infrastructure\Adapter\Out\Persistence\Models\AccountModel;
use Modules\Account\Infrastructure\Adapter\Out\Persistence\Models\ActivityModel;

class AccountMapper
{
    /**
     * @param  Collection<ActivityModel>  $activityModels
     */
    public function mapToDomainEntity(
        AccountModel $accountModel,
        Collection $activityModels,
        int $withdrawalBalance,
        int $depositBalance,
    ): Account {
        $baselineBalance = Money::subtract(
            Money::of($depositBalance),
            Money::of($withdrawalBalance),
        );

        return Account::withId(
            new AccountId($accountModel->id),
            $baselineBalance,
            $this->mapToActivityWindow($activityModels),
        );
    }

    /**
     * @param  Collection<ActivityModel>  $activityModels
     */
    public function mapToActivityWindow(Collection $activityModels): ActivityWindow
    {
        $activityEntities = $activityModels->map(fn (ActivityModel $activity) => new Activity(
            new ActivityId($activity->id),
            new AccountId($activity->owner_account_id),
            new AccountId($activity->source_account_id),
            new AccountId($activity->target_account_id),
            Carbon::parse($activity->created_at),
            Money::of($activity->amount),
        ));

        return new ActivityWindow(...$activityEntities);
    }

    public function mapToModel(Activity $activity): ActivityModel
    {
        return new ActivityModel([
            'id' => $activity->id->isNull() ? null : $activity->id->value,
            'created_at' => $activity->timestamp,
            'owner_account_id' => $activity->ownerAccountId->value,
            'source_account_id' => $activity->sourceAccountId->value,
            'target_account_id' => $activity->targetAccountId->value,
            'amount' => $activity->money->amount,
        ]);
    }
}
