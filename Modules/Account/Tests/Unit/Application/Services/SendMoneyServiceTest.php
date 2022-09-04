<?php

namespace Modules\Account\Tests\Unit\Application\Services;

use Mockery;
use Mockery\MockInterface;
use Modules\Account\Application\Port\In\SendMoneyCommand;
use Modules\Account\Application\Port\In\SendMoneyUseCase;
use Modules\Account\Application\Port\Out\AccountLock;
use Modules\Account\Application\Port\Out\LoadAccountPort;
use Modules\Account\Application\Port\Out\UpdateAccountStatePort;
use Modules\Account\Application\Services\MoneyTransferProperties;
use Modules\Account\Application\Services\SendMoneyService;
use Modules\Account\Domain\ActivityWindow;
use Modules\Account\Domain\Entities\Account;
use Modules\Account\Domain\ValueObjects\AccountId;
use Modules\Account\Domain\ValueObjects\Money;
use Modules\Account\Tests\Common\ActivityTestData;
use Tests\TestCase;

class SendMoneyServiceTest extends TestCase
{
    /** @var MockInterface|LoadAccountPort */
    protected $loadAccountPort;

    /** @var MockInterface|UpdateAccountStatePort */
    protected $updateAccountStatePort;

    /** @var MockInterface|AccountLock */
    protected $accountLock;

    /** @var SendMoneyUseCase */
    protected $sendMoneyService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadAccountPort = Mockery::mock(LoadAccountPort::class);
        $this->updateAccountStatePort = Mockery::mock(UpdateAccountStatePort::class);
        $this->accountLock = Mockery::mock(AccountLock::class);
        $this->sendMoneyService = new SendMoneyService(
            new MoneyTransferProperties(),
            $this->loadAccountPort,
            $this->updateAccountStatePort,
            $this->accountLock,
        );
    }

    /**
     * @test
     */
    public function givenWithdrawalFails_thenOnlySourceAccountIsLockedAndReleased(): void
    {
        $sourceAccountId = new AccountId(41);
        $sourceAccount = $this->givenAnAccountWithId($sourceAccountId);

        $targetAccountId = new AccountId(41);
        $targetAccount = $this->givenAnAccountWithId($targetAccountId);

        $this->givenWithdrawalWillFail($sourceAccount);
        $this->givenDepositWillSucceed($targetAccount);

        $command = new SendMoneyCommand(
            sourceAccountId: $sourceAccountId,
            targetAccountId: $targetAccountId,
            money: Money::of(300),
        );

        $this->accountLock->shouldReceive('lockAccount')->with($sourceAccountId)->once();
        $this->accountLock->shouldReceive('releaseAccount')->with($sourceAccountId)->once();
        $this->accountLock->shouldNotReceive('lockAccount')->with($targetAccountId);

        $success = $this->sendMoneyService->sendMoney($command);

        $this->assertFalse($success);
    }

    /**
     * @test
     */
    public function transactionSucceeds(): void
    {
        $sourceAccount = $this->givenSourceAccount();
        $targetAccount = $this->givenTargetAccount();

        $this->givenWithdrawalWillSucceed($sourceAccount);
        $this->givenDepositWillSucceed($targetAccount);

        $money = Money::of(500);

        $command = new SendMoneyCommand(
            sourceAccountId: $sourceAccount->id,
            targetAccountId: $targetAccount->id,
            money: Money::of(300),
        );

        $sourceAccountId = $sourceAccount->id;
        $targetAccountId = $targetAccount->id;

        $this->accountLock->shouldReceive('lockAccount')->with($sourceAccountId)->once();
        $sourceAccount->shouldReceive('withdraw')->with($money, $targetAccountId);
        $this->accountLock->shouldReceive('releaseAccount')->with($sourceAccountId)->once();

        $this->accountLock->shouldReceive('lockAccount')->with($targetAccountId)->once();
        $sourceAccount->shouldReceive('deposit')->with($money, $sourceAccountId);
        $this->accountLock->shouldReceive('releaseAccount')->with($targetAccountId)->once();

        $this->thenAccountsShouldUpdate($sourceAccount, $targetAccount);

        $success = $this->sendMoneyService->sendMoney($command);

        $this->assertTrue($success);
    }

    /**
     * @param  Account|MockInterface  $sourceAccount
     * @param  Account|MockInterface  $targetAccount
     */
    private function thenAccountsShouldUpdate($sourceAccount, $targetAccount): void
    {
        $this->updateAccountStatePort->shouldReceive('updateActivities')->with($sourceAccount)->once();
        $this->updateAccountStatePort->shouldReceive('updateActivities')->with($targetAccount)->once();
    }

    /**
     * @param  Account|MockInterface  $account
     */
    private function givenDepositWillSucceed($account): void
    {
        $account->shouldReceive('deposit')->andReturn(true);
    }

    /**
     * @param  Account|MockInterface  $account
     */
    private function givenWithdrawalWillFail($account): void
    {
        $account->shouldReceive('withdraw')->andReturn(false);
    }

    /**
     * @param  Account|MockInterface  $account
     */
    private function givenWithdrawalWillSucceed($account): void
    {
        $account->shouldReceive('withdraw')->andReturn(true);
    }

    /**
     * @return Account|MockInterface
     */
    private function givenTargetAccount()
    {
        return $this->givenAnAccountWithId(new AccountId(42));
    }

    /**
     * @return Account|MockInterface
     */
    private function givenSourceAccount()
    {
        return $this->givenAnAccountWithId(new AccountId(41));
    }

    /**
     * @return Account|MockInterface
     */
    private function givenAnAccountWithId(AccountId $id)
    {
        $account = Mockery::mock(Account::class, [
            $id,
            Money::of(1),
            new ActivityWindow(
                ActivityTestData::defaultActivity()->build(),
            ),
        ]);
        $this->loadAccountPort->shouldReceive('loadAccount')
            ->withArgs(fn (AccountId $accountId) => $accountId == $id)->andReturn($account);

        return $account;
    }
}
