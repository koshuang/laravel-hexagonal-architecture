<?php

namespace Modules\Account\Tests\Unit\Infrastructure\Adapter\Out\Persistence;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Account\Domain\ActivityWindow;
use Modules\Account\Domain\Entities\Account;
use Modules\Account\Domain\ValueObjects\AccountId;
use Modules\Account\Domain\ValueObjects\Money;
use Modules\Account\Domain\ValueObjects\NullActivityId;
use Modules\Account\Infrastructure\Adapter\Out\Persistence\AccountPersistenceAdapter;
use Modules\Account\Infrastructure\Adapter\Out\Persistence\Models\AccountModel;
use Modules\Account\Infrastructure\Adapter\Out\Persistence\Models\ActivityModel;
use Modules\Account\Tests\Common\AccountTestData;
use Modules\Account\Tests\Common\ActivityTestData;
use Tests\TestCase;

class AccountPersistenceAdapterTest extends TestCase
{
    use RefreshDatabase;

    protected AccountPersistenceAdapter $adapterUnderTest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adapterUnderTest = app(AccountPersistenceAdapter::class);
    }

    /** @test */
    public function loadsAccount(): void
    {
        $this->prepareForLoadsAccount();

        /** @var Account */
        $accountEntity = $this->adapterUnderTest->loadAccount(new AccountId(1), Carbon::parse('2022/09/02'));

        $this->assertEquals(3, count($accountEntity->activityWindow->activities));
        $this->assertEquals(600, $accountEntity->calculateBalance()->amount);
    }

    /** @test */
    public function updatesActivities(): void
    {
        $sourceAccountModel = AccountModel::factory()->create();
        $targetAccountModel = AccountModel::factory()->create();

        $sourceAccountId = new AccountId($sourceAccountModel->id);
        $targetAccountId = new AccountId($targetAccountModel->id);

        $account = AccountTestData::defaultAccount()
            ->withAccountId($sourceAccountId)
            ->withBaselineBalance(Money::of(555))
            ->withActivityWindow(
                new ActivityWindow(
                    ActivityTestData::defaultActivity()
                        ->withId(new NullActivityId())
                        ->withOwnerAccount($sourceAccountId)
                        ->withSourceAccount($sourceAccountId)
                        ->withTargetAccount($targetAccountId)
                        ->withMoney(Money::of(1))
                        ->build()
                )
            )
            ->build();

        $this->adapterUnderTest->updateActivities($account);

        $this->assertEquals(1, ActivityModel::count());

        $activityModel = ActivityModel::firstOrFail();

        $this->assertEquals(1, $activityModel->amount);
    }

    private function prepareForLoadsAccount(): void
    {
        $account = AccountModel::factory()
            ->has(
                ActivityModel::factory()
                    ->count(3)
                    ->in()
                    ->state([
                        'created_at' => '2022/09/01',
                        'amount' => 500,
                    ])
            )
            ->create();

        ActivityModel::factory()
            ->count(3)
            ->out()
            ->state(function (array $attributes) use ($account) {
                return [
                    'owner_account_id' => $account->id,
                    'source_account_id' => $account->id,
                    'created_at' => '2022/09/03',
                    'amount' => 300,
                ];
            })
            ->create();
    }
}
