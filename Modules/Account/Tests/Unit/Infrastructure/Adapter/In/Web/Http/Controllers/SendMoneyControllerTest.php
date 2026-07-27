<?php

namespace Modules\Account\Tests\Unit\Infrastructure\Adapter\In\Web\Http\Controllers;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Mockery;
use Mockery\MockInterface;
use Modules\Account\Application\Port\In\SendMoneyCommand;
use Modules\Account\Application\Port\In\SendMoneyUseCase;
use Modules\Account\Domain\ValueObjects\AccountId;
use Modules\Account\Domain\ValueObjects\Money;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversNothing]
class SendMoneyControllerTest extends TestCase
{
    use WithoutMiddleware;

    #[Test]
    public function send_money(): void
    {
        $sourceAccountId = 41;
        $targetAccountId = 42;
        $amount = 500;

        /** @var SendMoneyUseCase|MockInterface */
        $sendMoneyUseCase = Mockery::spy(SendMoneyUseCase::class);
        $this->app->instance(SendMoneyUseCase::class, $sendMoneyUseCase);

        $url = "/api/accounts/send/{$sourceAccountId}/{$targetAccountId}/{$amount}";
        $response = $this->json('POST', $url);

        $response->assertStatus(200);

        $sendMoneyUseCase->shouldHaveReceived('sendMoney')
            ->withArgs(
                fn ($command) => $command == new SendMoneyCommand(
                    sourceAccountId: new AccountId($sourceAccountId),
                    targetAccountId: new AccountId($targetAccountId),
                    money: new Money($amount),
                ),
            )
            ->once();
    }
}
