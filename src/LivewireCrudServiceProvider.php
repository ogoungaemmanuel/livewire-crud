<?php

namespace Xslainadmin\LivewireCrud;;

use Xslainadmin\LivewireCrud\Commands\LivewireCrudGenerator;
use Xslainadmin\LivewireCrud\Commands\TraditionalCrudGenerator;
use Xslainadmin\LivewireCrud\Commands\LivewireInstall;
use Illuminate\Support\ServiceProvider;

class LivewireCrudServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
			//Publishing config file
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('livewire-crud.php'),
            ], 'config');

            // Registering package commands.
            $this->commands([
				LivewireCrudGenerator::class,
				TraditionalCrudGenerator::class,
				LivewireInstall::class,
			]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'livewire-crud');

        // Register the main class to use with the facade
        $this->app->singleton('livewire-crud', function () {
            return new LivewireCrud;
        });
    }
}
