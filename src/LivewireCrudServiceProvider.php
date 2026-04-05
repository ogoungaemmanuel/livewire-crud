<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud;

use Illuminate\Support\ServiceProvider;
use Xslainadmin\LivewireCrud\Commands\LivewireAuthGenerator;
use Xslainadmin\LivewireCrud\Commands\LivewireCrudGenerator;
use Xslainadmin\LivewireCrud\Commands\LivewireLayoutGenerator;
use Xslainadmin\LivewireCrud\Commands\LivewireCrudNew;
use Xslainadmin\LivewireCrud\Commands\LivewireGenerateMakeCommand;
use Xslainadmin\LivewireCrud\Commands\LivewireInstall;
use Xslainadmin\LivewireCrud\Commands\LivewireMigrationGenerator;
use Xslainadmin\LivewireCrud\Commands\MakeComponentCommand;
use Xslainadmin\LivewireCrud\Commands\MakeModuleCommand;
use Xslainadmin\LivewireCrud\Commands\MakeResourceCommand;
use Xslainadmin\LivewireCrud\Contracts\CrudGeneratorInterface;
use Xslainadmin\LivewireCrud\LivewireCrud;
use Xslainadmin\LivewireCrud\ResourceRegistry;

class LivewireCrudServiceProvider extends ServiceProvider
{
    /**
     * All package commands.
     *
     * @var array<int, class-string>
     */
    protected array $commands = [
        LivewireCrudGenerator::class,
        LivewireCrudNew::class,
        LivewireAuthGenerator::class,
        LivewireLayoutGenerator::class,
        LivewireInstall::class,
        LivewireGenerateMakeCommand::class,
        LivewireMigrationGenerator::class,
        MakeComponentCommand::class,
        MakeModuleCommand::class,
        MakeResourceCommand::class,
    ];

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        // Resource auto-discovery (runs on every request when enabled)
        ResourceRegistry::discoverFromConfig();

        if ($this->app->runningInConsole()) {
            // Config
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('livewire-crud.php'),
            ], ['livewire-crud', 'livewire-crud-config']);

            // Stubs
            $this->publishes([
                __DIR__.'/stubs' => base_path('stubs/livewire-crud'),
            ], ['livewire-crud', 'livewire-crud-stubs']);

            // Register package commands
            $this->commands($this->commands);
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Merge package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'livewire-crud');

        // Register the main class to use with the facade
        $this->app->singleton('livewire-crud', static fn (): LivewireCrud => new LivewireCrud());

        // Resource registry — managed as a plain static class; the singleton
        // here lets Laravel's container resolve it by class name if needed.
        $this->app->singleton(ResourceRegistry::class, static fn (): ResourceRegistry => new ResourceRegistry());
        $this->app->alias(ResourceRegistry::class, 'livewire-crud.registry');

        // Bind CrudGeneratorInterface to the concrete generator command.
        $this->app->bind(CrudGeneratorInterface::class, LivewireCrudGenerator::class);
    }

    /**
     * Get provided services (improves deferred loading clarity in IDE).
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return ['livewire-crud', 'livewire-crud.registry'];
    }
}

