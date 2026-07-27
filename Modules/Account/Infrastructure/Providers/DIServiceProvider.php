<?php

namespace Modules\Account\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Account\Application\Port\In\SendMoneyUseCase;
use Modules\Account\Application\Port\Out\AccountLock;
use Modules\Account\Application\Port\Out\LoadAccountPort;
use Modules\Account\Application\Port\Out\UpdateAccountStatePort;
use Modules\Account\Application\Services\NoOpAccountLock;
use Modules\Account\Application\Services\SendMoneyService;
use Modules\Account\Infrastructure\Adapter\Out\Persistence\AccountPersistenceAdapter;

class DIServiceProvider extends ServiceProvider
{
    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     */
    public function boot() {}

    public function register(): void
    {
        // NOTE: because use cases will depend on out ports, out ports need register first
        $this->injectOutPorts();
        $this->injectUseCases();
    }

    protected function injectUseCases(): void
    {
        $this->app->instance(SendMoneyUseCase::class, $this->app->make(SendMoneyService::class));
    }

    protected function injectOutPorts(): void
    {
        $this->app->instance(LoadAccountPort::class, $this->app->make(AccountPersistenceAdapter::class));
        $this->app->instance(UpdateAccountStatePort::class, $this->app->make(AccountPersistenceAdapter::class));
        $this->app->instance(AccountLock::class, $this->app->make(NoOpAccountLock::class));
    }
}
