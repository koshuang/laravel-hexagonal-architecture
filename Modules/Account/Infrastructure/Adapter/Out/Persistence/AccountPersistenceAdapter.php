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
use Modules\Account\Infrastructure\Adapter\Out\Persistence\Repositories\ActivityRepository;

class AccountPersistenceAdapter implements LoadAccountPort, UpdateAccountStatePort
{
    public function __construct(
        private readonly AccountMapper $accountMapper,
        private readonly ActivityRepository $activityRepository,
    ) {
    }

    public function loadAccount(AccountId $accountId, Carbon $baselineDate): Account
    {
        $accountModel = AccountModel::findOrFail($accountId->value);

        $activityModels = $this->activityRepository->findByOwnerSince(
            $accountId->value,
            $baselineDate,
        );

        $withdrawalBalance = $this->activityRepository->getWithdrawalBalanceUntil(
            $accountId->value,
            $baselineDate,
        );

        $depositBalance = $this->activityRepository->getDepositBalanceUntil(
            $accountId->value,
            $baselineDate,
        );

        return $this->accountMapper->mapToDomainEntity(
            $accountModel,
            $activityModels,
            $withdrawalBalance,
            $depositBalance,
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
