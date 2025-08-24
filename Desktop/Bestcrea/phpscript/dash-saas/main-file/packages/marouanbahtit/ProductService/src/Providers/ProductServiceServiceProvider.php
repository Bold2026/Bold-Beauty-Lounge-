<?php

namespace Workdo\ProductService\Providers;

use Illuminate\Support\ServiceProvider;
use Workdo\ProductService\Providers\EventServiceProvider;
use Workdo\ProductService\Providers\RouteServiceProvider;

class ProductServiceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    protected $moduleName = 'ProductService';

    protected $moduleNameLower = 'product-service';

    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
        $this->app->register(EventServiceProvider::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'product-service');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        $this->registerTranslations();
    }

    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', $this->moduleNameLower);
            $this->loadJsonTranslationsFrom(__DIR__.'/../Resources/lang');
        }
    }
}
