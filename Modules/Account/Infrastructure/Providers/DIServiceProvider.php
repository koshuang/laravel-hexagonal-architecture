<?php

namespace Modules\Account\Infrastructure\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Modules\Account\Application\Port\In\SendMoneyUseCase;
use Modules\Account\Application\Port\Out\LoadAccountPort;
use Modules\Account\Application\Port\Out\UpdateAccountStatePort;
use Modules\Account\Application\Services\SendMoneyService;
use Modules\Account\Infrastructure\Adapter\Out\Persistence\AccountPersistenceAdapter;

class DIServiceProvider extends ServiceProvider
{
    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    public function register(): void
    {
        // NOTE: because use cases will depend on out ports, out ports need register first
        $this->injectOutPorts();
        $this->injectUseCases();
    }

    protected function injectUseCases(): void
    {
        App::instance(SendMoneyUseCase::class, app(SendMoneyService::class));
    }

    protected function injectOutPorts(): void
    {
        App::instance(LoadAccountPort::class, app(AccountPersistenceAdapter::class));
        App::instance(UpdateAccountStatePort::class, app(AccountPersistenceAdapter::class));
    }
}
