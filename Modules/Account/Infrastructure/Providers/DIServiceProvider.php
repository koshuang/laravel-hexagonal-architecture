<?php

namespace Modules\Account\Infrastructure\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Modules\Account\Application\Port\In\SendMoneyUseCase;
use Modules\Account\Application\Services\SendMoneyService;

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
        $this->injectUseCases();
    }

    protected function injectUseCases(): void
    {
        App::instance(SendMoneyUseCase::class, app(SendMoneyService::class));
    }
}
