<?php

namespace Modules\Account\Tests\Unit\Infrastructure\Adapter\Out\Persistence\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Account\Infrastructure\Adapter\Out\Persistence\Models\AccountModel;
use Modules\Account\Infrastructure\Adapter\Out\Persistence\Models\ActivityModel;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class AccountModelTest extends TestCase
{
    use RefreshDatabase;

    private AccountModel $account;

    public function setUp(): void
    {
        parent::setUp();

        $this->account = AccountModel::factory()->create();
        ActivityModel::factory()->create([
            'owner_account_id' => $this->account->id,
        ]);
        $this->account->refresh();
    }

    #[Test]
    public function properties(): void
    {
        $this->assertNotNull($this->account->id);
    }

    #[Test]
    public function relations(): void
    {
        $this->assertEquals(1, $this->account->activityModels->count());
    }
}
