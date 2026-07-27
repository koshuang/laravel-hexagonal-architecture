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
class ActivityModelTest extends TestCase
{
    use RefreshDatabase;

    private AccountModel $account;
    private ActivityModel $activity;

    public function setUp(): void
    {
        parent::setUp();

        $this->account = AccountModel::factory()->create();
        $this->activity = ActivityModel::factory()->create([
            'owner_account_id' => $this->account->id,
        ]);
    }

    #[Test]
    public function properties(): void
    {
        $this->assertNotNull($this->activity->id);
        $this->assertNotNull($this->activity->owner_account_id);
        $this->assertNotNull($this->activity->source_account_id);
        $this->assertNotNull($this->activity->target_account_id);
        $this->assertNotNull($this->activity->amount);
    }

    #[Test]
    public function relations(): void
    {
        $this->assertEquals($this->account->id, $this->activity->account?->id);
    }
}
