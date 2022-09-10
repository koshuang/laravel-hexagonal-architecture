<?php

namespace Modules\Account\Tests\Unit\Infrastructure\Adapter\Out\Persistence;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Account\Domain\Entities\Account;
use Modules\Account\Domain\ValueObjects\AccountId;
use Modules\Account\Infrastructure\Adapter\Out\Persistence\AccountPersistenceAdapter;
use Modules\Account\Infrastructure\Adapter\Out\Persistence\Models\AccountModel;
use Modules\Account\Infrastructure\Adapter\Out\Persistence\Models\ActivityModel;
use Tests\TestCase;

class AccountPersistenceAdapterTest extends TestCase
{
    use RefreshDatabase;

    protected AccountPersistenceAdapter $adapter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adapter = app(AccountPersistenceAdapter::class);
    }

    /** @test */
    public function loadsAccount(): void
    {
        $this->prepareForLoadsAccount();

        /** @var Account */
        $accountEntity = $this->adapter->loadAccount(new AccountId(1), Carbon::parse('2022/09/02'));

        $this->assertEquals(3, count($accountEntity->activityWindow->activities));
        $this->assertEquals(600, $accountEntity->calculateBalance()->amount);
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
