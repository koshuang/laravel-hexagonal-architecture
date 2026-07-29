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

/**
 * @internal
 */
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

        $sendMoneyUseCase = $this->mockSendMoneyUseCase();

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

    // === EDGE CASES ===

    #[Test]
    public function send_money_to_same_account(): void
    {
        $accountId = 41;
        $amount = 500;

        $sendMoneyUseCase = $this->mockSendMoneyUseCase();

        $url = "/api/accounts/send/{$accountId}/{$accountId}/{$amount}";
        $response = $this->json('POST', $url);

        $response->assertStatus(200);

        // Use case is called — the domain checks for same-account transfer
        $sendMoneyUseCase->shouldHaveReceived('sendMoney')
            ->withArgs(
                fn ($command) => $command == new SendMoneyCommand(
                    sourceAccountId: new AccountId($accountId),
                    targetAccountId: new AccountId($accountId),
                    money: new Money($amount),
                ),
            )
            ->once();
    }

    #[Test]
    public function send_money_with_zero_amount(): void
    {
        $sourceAccountId = 41;
        $targetAccountId = 42;
        $amount = 0;

        $sendMoneyUseCase = $this->mockSendMoneyUseCase();

        $url = "/api/accounts/send/{$sourceAccountId}/{$targetAccountId}/{$amount}";
        $response = $this->json('POST', $url);

        $response->assertStatus(200);

        // Backend Money accepts zero, so the controller should pass it through
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

    #[Test]
    public function send_money_with_negative_amount(): void
    {
        $sourceAccountId = 41;
        $targetAccountId = 42;
        $amount = -100;

        $sendMoneyUseCase = $this->mockSendMoneyUseCase();

        $url = "/api/accounts/send/{$sourceAccountId}/{$targetAccountId}/{$amount}";
        $response = $this->json('POST', $url);

        $response->assertStatus(200);

        // Backend Money allows negative values, so controller passes it through
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

    #[Test]
    public function send_money_with_large_amount(): void
    {
        $sourceAccountId = 41;
        $targetAccountId = 42;
        $amount = 1_000_000_000;

        $sendMoneyUseCase = $this->mockSendMoneyUseCase();

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

    #[Test]
    public function send_money_to_nonexistent_source_account(): void
    {
        // The use case is mocked, so it returns null regardless of account existence.
        // Existence validation happens in the use case/domain layer, not the controller.
        $sendMoneyUseCase = $this->mockSendMoneyUseCase();

        $url = '/api/accounts/send/99999/1/100';
        $response = $this->json('POST', $url);

        // Controller always returns 200 with mocked use case
        $response->assertStatus(200);

        // Verify the command was still constructed with the given IDs
        $sendMoneyUseCase->shouldHaveReceived('sendMoney')
            ->withArgs(
                fn ($command) => $command == new SendMoneyCommand(
                    sourceAccountId: new AccountId(99999),
                    targetAccountId: new AccountId(1),
                    money: new Money(100),
                ),
            )
            ->once();
    }

    private function mockSendMoneyUseCase(): MockInterface&SendMoneyUseCase
    {
        /** @var SendMoneyUseCase&MockInterface */
        $mock = Mockery::spy(SendMoneyUseCase::class);
        $this->app->instance(SendMoneyUseCase::class, $mock);

        return $mock;
    }
}
