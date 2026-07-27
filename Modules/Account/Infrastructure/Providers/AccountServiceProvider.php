<?php

namespace Modules\Account\Infrastructure\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Override;

class AccountServiceProvider extends ServiceProvider
{
    /**
     * @var string
     */
    protected $moduleName = 'Account';

    /**
     * @var string
     */
    protected $moduleNameLower = 'account';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerCommands();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Infrastructure/Adapter/Out/Persistence/Migrations'));
    }

    /**
     * Register the service provider.
     */
    #[Override]
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
        $this->app->register(DIServiceProvider::class);
    }

    public function registerCommands(): void
    {
        $this->commands([]);
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);

        $sourcePath = module_path($this->moduleName, 'Infrastructure/Adapter/In/Web/Resources/views');

        $this->publishes([
            $sourcePath => $viewPath,
        ], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Infrastructure/Adapter/In/Web/Resources/lang'), $this->moduleNameLower);
            $this->loadJsonTranslationsFrom(module_path($this->moduleName, 'Infrastructure/Adapter/In/Web/Resources/lang'));
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    #[Override]
    public function provides(): array
    {
        return [];
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->publishes([
            module_path($this->moduleName, 'Infrastructure/Config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Infrastructure/Config/config.php'),
            $this->moduleNameLower,
        );
    }

    /**
     * @return array<int, string>
     */
    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (Config::get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }

        return $paths;
    }
}
