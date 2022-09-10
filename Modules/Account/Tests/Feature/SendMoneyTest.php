<?php

namespace Modules\Account\Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Testing\TestResponse;
use Modules\Account\Application\Port\Out\LoadAccountPort;
use Modules\Account\Domain\Entities\Account;
use Modules\Account\Domain\ValueObjects\AccountId;
use Modules\Account\Domain\ValueObjects\Money;
use Modules\Account\Infrastructure\Adapter\Out\Persistence\Models\AccountModel;
use Modules\Account\Infrastructure\Adapter\Out\Persistence\Models\ActivityModel;
use Tests\TestCase;

class SendMoneyTest extends TestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;

    private LoadAccountPort $loadAccountPort;
    private int $sourceAccountId;
    private int $targetAccountId;

    public function setUp(): void
    {
        parent::setUp();

        $this->loadAccountPort = $this->app->make(LoadAccountPort::class);

        $this->sourceAccountId = AccountModel::factory()
            ->has(
                ActivityModel::factory()
                    ->count(3)
                    ->in()
                    ->state([
                        'created_at' => '2022/09/01',
                        'amount' => 500,
                    ])
            )
            ->create()->id;
        $this->targetAccountId = AccountModel::factory()->create()->id;
    }

    /**
     * @test
     */
    public function sendMoney(): void
    {
        $initialSourceBalance = $this->sourceAccount()->calculateBalance();
        $initialTargetBalance = $this->targetAccount()->calculateBalance();

        $response = $this->whenSendMoney(
            $this->sourceAccountId(),
            $this->targetAccountId(),
            $this->transferAmount()
        );

        $response->assertStatus(200);

        $this->assertEquals(
            $initialSourceBalance->minus($this->transferAmount()),
            $this->sourceAccount()->calculateBalance()
        );

        $this->assertEquals(
            $initialTargetBalance->plus($this->transferAmount()),
            $this->targetAccount()->calculateBalance()
        );
    }

    private function whenSendMoney(
        AccountId $sourceAccountId,
            AccountId $targetAccountId,
            Money $amount
    ): TestResponse {
        $url = "/api/accounts/send/$sourceAccountId/$targetAccountId/$amount";
        $response = $this->json('POST', $url);

        return $response;
    }

    private function sourceAccount(): Account
    {
        return $this->loadAccount($this->sourceAccountId());
    }

    private function targetAccount(): Account
    {
        return $this->loadAccount($this->targetAccountId());
    }

    private function transferAmount(): Money
    {
        return Money::of(500);
    }

    private function loadAccount(AccountId $accountId): Account
    {
        return $this->loadAccountPort->loadAccount($accountId, Carbon::now());
    }

    private function sourceAccountId(): AccountId
    {
        return new AccountId($this->sourceAccountId);
    }

    private function targetAccountId(): AccountId
    {
        return new AccountId($this->targetAccountId);
    }
}
