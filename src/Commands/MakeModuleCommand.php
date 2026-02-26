<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

/**
 * MakeModuleCommand — full nWidart-compatible module scaffold with livewire-crud integration.
 *
 * Inspired by nWidart/laravel-modules `module:make`, this command creates:
 *
 *  Directory structure
 *  ───────────────────
 *  Modules/{Name}/
 *    app/
 *      Http/Controllers/
 *      Models/
 *    Livewire/          ← Livewire components live here
 *    Exports/
 *    Imports/
 *    Notifications/
 *    Providers/
 *    Database/
 *      Migrations/
 *      Seeders/
 *      factories/
 *    resources/
 *      views/
 *        layouts/
 *        livewire/
 *        auth/
 *      css/
 *      js/
 *      lang/
 *    routes/
 *    config/
 *    tests/
 *      Feature/
 *      Unit/
 *
 *  Generated files
 *  ───────────────
 *  module.json, composer.json, vite.config.js, package.json
 *  Providers/{Name}ServiceProvider.php
 *  Providers/RouteServiceProvider.php
 *  Providers/EventServiceProvider.php
 *  routes/web.php, routes/api.php
 *  config/config.php
 *  resources/views/layouts/backend.blade.php
 *  resources/views/layouts/master.blade.php
 *  resources/js/app.js, resources/css/app.css
 *  README.md
 *
 *  Bootstrap registration
 *  ──────────────────────
 *  Automatically adds the module's ServiceProvider to bootstrap/providers.php
 *  (or config/app.php providers array as a fallback).
 *
 * Usage
 * ─────
 *   php artisan module:scaffold Shop
 *   php artisan module:scaffold Blog --plain
 *   php artisan module:scaffold Inventory --author="John Doe" --email="john@example.com"
 *   php artisan module:scaffold Orders --no-register
 */
final class MakeModuleCommand extends Command
{
    protected $signature = 'module:scaffold
        {name              : Module name in PascalCase (e.g. Shop, BlogPosts)}
        {--plain           : Skip stub files and only create the directory skeleton}
        {--force           : Overwrite existing files without prompting}
        {--no-register     : Do not inject the ServiceProvider into bootstrap/providers.php}
        {--author=         : Author name for composer.json}
        {--email=          : Author email for composer.json}
        {--description=    : Module description}';

    protected $description = 'Scaffold a new nWidart-compatible module with full livewire-crud integration';

    private Filesystem $files;

    /** Module name exactly as given (PascalCase). */
    private string $moduleName;
    /** Lowercase module name used for namespaces and route prefixes. */
    private string $lower;
    /** Studly-case module name. */
    private string $studly;
    /** Absolute path to Modules/{Name}. */
    private string $modBase;

    // -----------------------------------------------------------------------
    // Entry point
    // -----------------------------------------------------------------------

    public function handle(): int
    {
        $this->files   = new Filesystem();
        $this->moduleName    = Str::studly($this->argument('name'));
        $this->lower   = Str::lower($this->moduleName);
        $this->studly  = $this->moduleName;
        $this->modBase = base_path("Modules/{$this->moduleName}");

        // Guard against accidental overwrite
        if ($this->files->isDirectory($this->modBase) && ! $this->option('force')) {
            if (! $this->confirm("Module [{$this->moduleName}] already exists. Overwrite files?", false)) {
                $this->components->warn("Aborted — no files were modified.");
                return self::SUCCESS;
            }
        }

        $this->renderBanner();

        // Step 1 — directories
        $this->components->task('Creating directory structure', fn () => $this->createDirectories());

        if (! $this->option('plain')) {
            // Step 2 — core module files
            $this->components->task('Generating module.json', fn () => $this->writeModuleJson());
            $this->components->task('Generating composer.json', fn () => $this->writeComposerJson());
            $this->components->task('Generating package.json', fn () => $this->writePackageJson());
            $this->components->task('Generating vite.config.js', fn () => $this->writeViteConfig());

            // Step 3 — PHP files
            $this->components->task('Generating ServiceProvider', fn () => $this->writeServiceProvider());
            $this->components->task('Generating RouteServiceProvider', fn () => $this->writeRouteServiceProvider());
            $this->components->task('Generating EventServiceProvider', fn () => $this->writeEventServiceProvider());

            // Step 4 — routes
            $this->components->task('Generating routes/web.php', fn () => $this->writeWebRoutes());
            $this->components->task('Generating routes/api.php', fn () => $this->writeApiRoutes());

            // Step 5 — config
            $this->components->task('Generating config/config.php', fn () => $this->writeModuleConfig());

            // Step 6 — views
            $this->components->task('Generating layout views', fn () => $this->writeLayouts());

            // Step 7 — assets
            $this->components->task('Generating frontend assets', fn () => $this->writeAssets());

            // Step 8 — README
            $this->components->task('Generating README.md', fn () => $this->writeReadme());
        }

        // Step 9 — bootstrap registration
        if (! $this->option('no-register')) {
            $this->components->task('Registering ServiceProvider', fn () => $this->registerProvider());
        }

        $this->printSummary();

        return self::SUCCESS;
    }

    // -----------------------------------------------------------------------
    // Directory scaffold
    // -----------------------------------------------------------------------

    private function createDirectories(): void
    {
        $dirs = [
            'app/Http/Controllers',
            'app/Http/Requests',
            'app/Models',
            'Livewire',
            'Livewire/Auth',
            'Exports',
            'Imports',
            'Notifications',
            'Emails',
            'Observers',
            'Policies',
            'Services',
            'Providers',
            'Database/Migrations',
            'Database/Seeders',
            'Database/factories',
            'resources/views/layouts',
            'resources/views/livewire',
            'resources/views/auth',
            'resources/views/components',
            'resources/views/partials',
            'resources/css',
            'resources/js',
            'resources/lang/en',
            'routes',
            'config',
            'tests/Feature',
            'tests/Unit',
        ];

        foreach ($dirs as $dir) {
            $this->files->ensureDirectoryExists("{$this->modBase}/{$dir}");
        }
    }

    // -----------------------------------------------------------------------
    // module.json
    // -----------------------------------------------------------------------

    private function writeModuleJson(): void
    {
        $path = "{$this->modBase}/module.json";
        if ($this->files->exists($path) && ! $this->option('force')) {
            return;
        }

        $this->files->put($path, json_encode([
            'name'        => $this->moduleName,
            'alias'       => $this->lower,
            'description' => $this->option('description') ?? "{$this->moduleName} module — generated by Livewire CRUD Enterprise",
            'keywords'    => [],
            'priority'    => 0,
            'providers'   => [
                "Modules\\{$this->moduleName}\\Providers\\{$this->moduleName}ServiceProvider",
            ],
            'aliases'     => [],
            'files'       => [],
            'livewire-crud' => [
                'version'    => \Xslainadmin\LivewireCrud\LivewireCrud::VERSION,
                'scaffolded' => now()->toIso8601String(),
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
    }

    // -----------------------------------------------------------------------
    // composer.json
    // -----------------------------------------------------------------------

    private function writeComposerJson(): void
    {
        $path   = "{$this->modBase}/composer.json";
        if ($this->files->exists($path) && ! $this->option('force')) {
            return;
        }

        $author = $this->option('author') ?? 'Developer';
        $email  = $this->option('email')  ?? 'dev@example.com';
        $desc   = $this->option('description') ?? "{$this->moduleName} module for Laravel";

        $this->files->put($path, json_encode([
            'name'        => 'modules/' . Str::kebab($this->moduleName),
            'description' => $desc,
            'authors'     => [['name' => $author, 'email' => $email]],
            'extra'       => [
                'laravel' => [
                    'providers' => [
                        "Modules\\{$this->moduleName}\\Providers\\{$this->moduleName}ServiceProvider",
                    ],
                ],
            ],
            'autoload'    => [
                'psr-4' => [
                    "Modules\\{$this->moduleName}\\" => '',
                ],
            ],
            'autoload-dev' => [
                'psr-4' => [
                    "Modules\\{$this->moduleName}\\Tests\\" => 'tests',
                ],
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
    }

    // -----------------------------------------------------------------------
    // package.json
    // -----------------------------------------------------------------------

    private function writePackageJson(): void
    {
        $path = "{$this->modBase}/package.json";
        if ($this->files->exists($path) && ! $this->option('force')) {
            return;
        }

        $this->files->put($path, json_encode([
            'private'  => true,
            'name'     => Str::kebab($this->moduleName),
            'scripts'  => [
                'dev'   => 'vite',
                'build' => 'vite build',
            ],
            'devDependencies' => [
                'axios'      => '^1.6',
                'laravel-vite-plugin' => '^1.0',
                'vite'       => '^5.0',
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
    }

    // -----------------------------------------------------------------------
    // vite.config.js
    // -----------------------------------------------------------------------

    private function writeViteConfig(): void
    {
        $path = "{$this->modBase}/vite.config.js";
        if ($this->files->exists($path) && ! $this->option('force')) {
            return;
        }

        $lower = $this->lower;
        $name  = $this->moduleName;

        $this->files->put($path, <<<JS
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'Modules/{$name}/resources/css/app.css',
                'Modules/{$name}/resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    build: {
        outDir: 'public/modules/{$lower}',
        emptyOutDir: true,
    },
});

JS);
    }

    // -----------------------------------------------------------------------
    // Providers
    // -----------------------------------------------------------------------

    private function writeServiceProvider(): void
    {
        $path = "{$this->modBase}/Providers/{$this->moduleName}ServiceProvider.php";
        if ($this->files->exists($path) && ! $this->option('force')) {
            return;
        }

        $name  = $this->moduleName;
        $lower = $this->lower;

        $this->files->put($path, <<<PHP
<?php

declare(strict_types=1);

namespace Modules\\{$name}\\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class {$name}ServiceProvider extends ServiceProvider
{
    /**
     * Module name / view namespace.
     */
    protected string \$moduleName = '{$name}';
    protected string \$moduleNameLower = '{$lower}';

    /**
     * Boot the module's services.
     */
    public function boot(): void
    {
        \$this->registerConfig();
        \$this->registerViews();
        \$this->loadMigrationsFrom(module_path(\$this->moduleName, 'Database/Migrations'));
        \$this->registerTranslations();
        \$this->app->register(RouteServiceProvider::class);
        \$this->registerLivewireComponents();
    }

    /**
     * Register the module's services.
     */
    public function register(): void
    {
        \$this->app->register(EventServiceProvider::class);
    }

    // ── Private helpers ───────────────────────────────────────────────────

    protected function registerConfig(): void
    {
        \$this->mergeConfigFrom(module_path(\$this->moduleName, 'config/config.php'), \$this->moduleNameLower);

        if (\$this->app->runningInConsole()) {
            \$this->publishes([
                module_path(\$this->moduleName, 'config/config.php') => config_path(\$this->moduleNameLower . '.php'),
            ], ['config', \$this->moduleNameLower . '-config']);
        }
    }

    protected function registerViews(): void
    {
        \$viewPath = resource_path('views/modules/' . \$this->moduleNameLower);
        \$sourcePath = module_path(\$this->moduleName, 'resources/views');

        \$this->publishes([\$sourcePath => \$viewPath], ['views', \$this->moduleNameLower . '-views']);
        \$this->loadViewsFrom(\$sourcePath, \$this->moduleNameLower);
    }

    protected function registerTranslations(): void
    {
        \$langPath = resource_path('lang/modules/' . \$this->moduleNameLower);
        if (is_dir(\$langPath)) {
            \$this->loadTranslationsFrom(\$langPath, \$this->moduleNameLower);
        } else {
            \$this->loadTranslationsFrom(module_path(\$this->moduleName, 'resources/lang'), \$this->moduleNameLower);
        }
    }

    /**
     * Discover and register all Livewire components in this module.
     * Components in Modules/{$name}/Livewire/*.php are registered as
     *   {$lower}::{component-name}
     */
    protected function registerLivewireComponents(): void
    {
        if (! class_exists(\\Livewire\\Livewire::class)) {
            return;
        }

        \$livewirePath = module_path(\$this->moduleName, 'Livewire');
        if (! is_dir(\$livewirePath)) {
            return;
        }

        \$files = \\Illuminate\\Support\\Facades\\File::allFiles(\$livewirePath);
        foreach (\$files as \$file) {
            \$relativeName = str_replace(['/', '.php'], ['\\\\', ''], \$file->getRelativePathname());
            \$class = "Modules\\\\{$name}\\\\Livewire\\\\{\$relativeName}";
            if (class_exists(\$class)) {
                \$alias = \$this->moduleNameLower . '::' . Str::kebab(str_replace('\\\\', '.', \$relativeName));
                Livewire::component(\$alias, \$class);
            }
        }
    }
}

PHP);
    }

    private function writeRouteServiceProvider(): void
    {
        $path = "{$this->modBase}/Providers/RouteServiceProvider.php";
        if ($this->files->exists($path) && ! $this->option('force')) {
            return;
        }

        $name  = $this->moduleName;
        $lower = $this->lower;

        $this->files->put($path, <<<PHP
<?php

declare(strict_types=1);

namespace Modules\\{$name}\\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The module's namespace to use when loading routes.
     */
    protected string \$moduleName = '{$name}';
    protected string \$moduleNameLower = '{$lower}';

    /**
     * Called before routes are registered.
     * Register any model bindings or pattern filters here.
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Define the routes for the module.
     */
    public function map(): void
    {
        \$this->mapWebRoutes();
        \$this->mapApiRoutes();
    }

    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->group(module_path(\$this->moduleName, '/routes/web.php'));
    }

    protected function mapApiRoutes(): void
    {
        Route::middleware('api')
            ->prefix('api')
            ->name('api.')
            ->group(module_path(\$this->moduleName, '/routes/api.php'));
    }
}

PHP);
    }

    private function writeEventServiceProvider(): void
    {
        $path = "{$this->modBase}/Providers/EventServiceProvider.php";
        if ($this->files->exists($path) && ! $this->option('force')) {
            return;
        }

        $name = $this->moduleName;

        $this->files->put($path, <<<PHP
<?php

declare(strict_types=1);

namespace Modules\\{$name}\\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the module.
     *
     * @var array<string, array<string>>
     */
    protected \$listen = [
        // \\Modules\\{$name}\\Events\\YourEvent::class => [
        //     \\Modules\\{$name}\\Listeners\\YourEventListener::class,
        // ],
    ];

    public function boot(): void
    {
        parent::boot();
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}

PHP);
    }

    // -----------------------------------------------------------------------
    // Routes
    // -----------------------------------------------------------------------

    private function writeWebRoutes(): void
    {
        $path = "{$this->modBase}/routes/web.php";
        if ($this->files->exists($path) && ! $this->option('force')) {
            return;
        }

        $name  = $this->moduleName;
        $lower = $this->lower;

        $this->files->put($path, <<<PHP
<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| {$name} Module Web Routes
|--------------------------------------------------------------------------
|
| Routes for the {$name} module. Livewire component views are served via
| Route::view() and injected below by the livewire-crud generator.
|
*/

Route::middleware(['web', 'auth'])
    ->prefix('{$lower}')
    ->name('{$lower}.')
    ->group(function () {
        Route::view('/', '{$lower}::index')->name('index');

        //Route Hooks - Do not delete//
    });

PHP);
    }

    private function writeApiRoutes(): void
    {
        $path = "{$this->modBase}/routes/api.php";
        if ($this->files->exists($path) && ! $this->option('force')) {
            return;
        }

        $name  = $this->moduleName;
        $lower = $this->lower;

        $this->files->put($path, <<<PHP
<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| {$name} Module API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['api', 'auth:sanctum'])
    ->prefix('{$lower}')
    ->name('{$lower}.api.')
    ->group(function () {
        // Define your {$name} API routes here
    });

PHP);
    }

    // -----------------------------------------------------------------------
    // Config
    // -----------------------------------------------------------------------

    private function writeModuleConfig(): void
    {
        $path = "{$this->modBase}/config/config.php";
        if ($this->files->exists($path) && ! $this->option('force')) {
            return;
        }

        $name  = $this->moduleName;
        $lower = $this->lower;

        $this->files->put($path, <<<PHP
<?php

return [

    /*
    |----------------------------------------------------------------------
    | {$name} Module Configuration
    |----------------------------------------------------------------------
    */

    'name'        => '{$name}',
    'enabled'     => true,
    'description' => '{$name} module — generated by Livewire CRUD Enterprise',

    /*
    |----------------------------------------------------------------------
    | Route & Navigation
    |----------------------------------------------------------------------
    */
    'route_prefix'  => '{$lower}',
    'nav_icon'      => 'bi-grid',
    'nav_label'     => '{$name}',
    'nav_sort'      => 10,

    /*
    |----------------------------------------------------------------------
    | Permissions
    |----------------------------------------------------------------------
    */
    'permissions' => [
        '{$lower}.view',
        '{$lower}.create',
        '{$lower}.edit',
        '{$lower}.delete',
    ],

];

PHP);
    }

    // -----------------------------------------------------------------------
    // Views / Layouts
    // -----------------------------------------------------------------------

    private function writeLayouts(): void
    {
        $lower = $this->lower;
        $name  = $this->moduleName;

        // ── backend layout (the main app wrapper) ─────────────────────────
        $backendPath = "{$this->modBase}/resources/views/layouts/backend.blade.php";
        if (! $this->files->exists($backendPath) || $this->option('force')) {
            $this->files->put($backendPath, <<<BLADE
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ \$title ?? config('{$lower}.nav_label', '{$name}') }} — {{ config('app.name') }}</title>
    @vite(['Modules/{$name}/resources/css/app.css', 'Modules/{$name}/resources/js/app.js'])
    @livewireStyles
</head>
<body>
    <div id="app">
        {{-- Sidebar navigation --}}
        @include('{$lower}::partials.sidebar')

        <main class="main-content">
            {{-- Flash notifications --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
            @stack('modals')
        </main>
    </div>

    @livewireScripts
</body>
</html>
BLADE);
        }

        // ── master layout (full-width, no sidebar) ─────────────────────────
        $masterPath = "{$this->modBase}/resources/views/layouts/master.blade.php";
        if (! $this->files->exists($masterPath) || $this->option('force')) {
            $this->files->put($masterPath, <<<BLADE
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ \$title ?? config('app.name') }}</title>
    @vite(['Modules/{$name}/resources/css/app.css', 'Modules/{$name}/resources/js/app.js'])
    @livewireStyles
</head>
<body>
    @yield('content')
    @livewireScripts
</body>
</html>
BLADE);
        }

        // ── sidebar partial stub ───────────────────────────────────────────
        $sidebarPath = "{$this->modBase}/resources/views/partials/sidebar.blade.php";
        if (! $this->files->exists($sidebarPath) || $this->option('force')) {
            $this->files->put($sidebarPath, <<<BLADE
{{-- Sidebar navigation for the {$name} module --}}
<nav class="sidebar">
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('{$lower}.*') ? 'active' : '' }}"
               href="{{ route('{$lower}.index') }}">
                <i class="bi bi-grid me-2"></i>
                {$name}
            </a>
        </li>
        {{-- Additional nav items are injected here by crud:generate --}}
        @include('{$lower}::partials.nav-items', ['prefix' => '{$lower}'])
    </ul>
</nav>
BLADE);
        }

        // ── nav-items partial stub ─────────────────────────────────────────
        $navItemsPath = "{$this->modBase}/resources/views/partials/nav-items.blade.php";
        if (! $this->files->exists($navItemsPath) || $this->option('force')) {
            $this->files->put($navItemsPath, <<<BLADE
{{-- Generated nav items are appended below this line --}}
{{--Nav Hooks - Do not delete--}}
BLADE);
        }

        // ── module index page ──────────────────────────────────────────────
        $indexPath = "{$this->modBase}/resources/views/index.blade.php";
        if (! $this->files->exists($indexPath) || $this->option('force')) {
            $this->files->put($indexPath, <<<BLADE
@extends('{$lower}::layouts.backend')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h2 class="h4 fw-bold mb-4">
                <i class="bi bi-grid me-2"></i>{$name} Dashboard
            </h2>
        </div>
    </div>
    {{-- Widget area — populated by livewire-crud widgets --}}
    <livewire:lazy-loading />
</div>
@endsection
BLADE);
        }
    }

    // -----------------------------------------------------------------------
    // Frontend assets
    // -----------------------------------------------------------------------

    private function writeAssets(): void
    {
        $lower = $this->lower;
        $name  = $this->moduleName;

        // CSS
        $cssPath = "{$this->modBase}/resources/css/app.css";
        if (! $this->files->exists($cssPath) || $this->option('force')) {
            $this->files->put($cssPath, <<<CSS
/* {$name} Module Styles
 * Extend or override global styles here.
 * Bootstrap 5 is loaded globally; import only module-specific styles.
 */

/* ── Layout ─────────────────────────────────────────────────────────────── */
.sidebar {
    min-height: 100vh;
    background-color: var(--bs-dark);
    padding: 1rem 0;
}

.sidebar .nav-link {
    color: rgba(255, 255, 255, .75);
    padding: .5rem 1.5rem;
}

.sidebar .nav-link:hover,
.sidebar .nav-link.active {
    color: #fff;
    background-color: rgba(255, 255, 255, .1);
    border-radius: .375rem;
}

.main-content {
    min-height: 100vh;
    background-color: #f8f9fa;
}
CSS);
        }

        // JS
        $jsPath = "{$this->modBase}/resources/js/app.js";
        if (! $this->files->exists($jsPath) || $this->option('force')) {
            $this->files->put($jsPath, <<<JS
/**
 * {$name} Module JavaScript
 *
 * Bootstrap 5 and Alpine.js are loaded globally via bootstrap stack.
 * Place module-specific JS here.
 */
import './bootstrap';

// Example: auto-dismiss flash alerts
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.alert-dismissible[data-autohide]').forEach(function (el) {
        const ms = parseInt(el.dataset.autohide, 10) || 4000;
        setTimeout(() => bootstrap.Alert.getOrCreateInstance(el).close(), ms);
    });
});

JS);
        }

        // Bootstrap JS shim
        $bootstrapJsPath = "{$this->modBase}/resources/js/bootstrap.js";
        if (! $this->files->exists($bootstrapJsPath) || $this->option('force')) {
            $this->files->put($bootstrapJsPath, <<<JS
/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */
import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

JS);
        }
    }

    // -----------------------------------------------------------------------
    // README
    // -----------------------------------------------------------------------

    private function writeReadme(): void
    {
        $path = "{$this->modBase}/README.md";
        if ($this->files->exists($path) && ! $this->option('force')) {
            return;
        }

        $name  = $this->moduleName;
        $lower = $this->lower;

        $this->files->put($path, <<<MD
# {$name} Module

> Generated by [Livewire CRUD Enterprise](https://github.com/ogoungaemmanuel/livewire-crud) v{$this->lcVersion()}

## Overview

The `{$name}` module follows the [nWidart/laravel-modules](https://nwidart.com/laravel-modules) convention and
is fully integrated with the **Livewire CRUD Enterprise** package.

## Directory Structure

```
Modules/{$name}/
├── app/
│   ├── Http/Controllers/
│   ├── Http/Requests/
│   └── Models/
├── Livewire/              ← Livewire components (generated by crud:generate)
├── Exports/
├── Imports/
├── Notifications/
├── Providers/
│   ├── {$name}ServiceProvider.php
│   ├── RouteServiceProvider.php
│   └── EventServiceProvider.php
├── Database/
│   ├── Migrations/
│   ├── Seeders/
│   └── factories/
├── resources/
│   ├── views/
│   │   ├── layouts/     ← backend.blade.php, master.blade.php
│   │   ├── livewire/    ← generated CRUD views
│   │   └── partials/    ← sidebar, nav-items
│   ├── css/app.css
│   ├── js/app.js
│   └── lang/
├── routes/
│   ├── web.php
│   └── api.php
├── config/config.php
├── module.json
├── composer.json
└── vite.config.js
```

## Generating CRUD

```bash
# Existing DB table
php artisan crud:generate {table} modern admin {$name}

# New table (with interactive column builder)
php artisan crud:new {table} modern admin {$name}
```

## Resource Registration

The module's `ServiceProvider` is registered in `bootstrap/providers.php`.
Make sure `Modules/{$name}` is in your `composer.json` autoload paths or run:

```bash
composer dump-autoload
```

## Configuration

Publish and customise the module config:

```bash
php artisan vendor:publish --tag={$lower}-config
```

Then edit `config/{$lower}.php`.
MD);
    }

    // -----------------------------------------------------------------------
    // Bootstrap / providers.php registration
    // -----------------------------------------------------------------------

    private function registerProvider(): void
    {
        $providerClass = "Modules\\{$this->moduleName}\\Providers\\{$this->moduleName}ServiceProvider";

        // Laravel 11+ — bootstrap/providers.php
        $bootstrapProviders = base_path('bootstrap/providers.php');
        if ($this->files->exists($bootstrapProviders)) {
            $content = $this->files->get($bootstrapProviders);
            if (str_contains($content, "Modules\\{$this->moduleName}")) {
                $this->line("  <fg=yellow>SKIP</>   — Already registered in bootstrap/providers.php");
                return;
            }

            $content = str_replace('];', "    {$providerClass}::class,\n];", $content);
            $this->files->put($bootstrapProviders, $content);
            $this->line("  <fg=green>UPDATED</> — bootstrap/providers.php");
            return;
        }

        // Laravel 10 fallback — config/app.php
        $appConfig = config_path('app.php');
        if ($this->files->exists($appConfig)) {
            $content = $this->files->get($appConfig);
            if (str_contains($content, "Modules\\{$this->moduleName}")) {
                $this->line("  <fg=yellow>SKIP</>   — Already registered in config/app.php");
                return;
            }

            // Insert after last App\ provider line
            $content = str_replace(
                'App\\Providers\\RouteServiceProvider::class,',
                "App\\Providers\\RouteServiceProvider::class,\n        {$providerClass}::class,",
                $content,
            );
            $this->files->put($appConfig, $content);
            $this->line("  <fg=green>UPDATED</> — config/app.php providers");
        } else {
            $this->components->warn("Could not auto-register provider. Add manually: {$providerClass}::class");
        }
    }

    // -----------------------------------------------------------------------
    // Summary
    // -----------------------------------------------------------------------

    private function printSummary(): void
    {
        $name  = $this->moduleName;
        $lower = $this->lower;

        $this->newLine();
        $this->components->success("Module [{$name}] scaffolded successfully!");
        $this->newLine();
        $this->line('  <options=bold>Directory:</>  <fg=yellow>Modules/' . $name . '</>');
        $this->newLine();
        $this->line('  <options=bold>Next steps:</>');
        $this->line("  1. Run <fg=green>composer dump-autoload</> to register the module's PSR-4 namespace.");
        $this->line("  2. Generate your first CRUD:");
        $this->line("       <fg=green>php artisan crud:generate {table} modern admin {$name}</>");
        $this->line("       <fg=green>php artisan crud:new {table} modern admin {$name}</> (no DB table needed)");
        $this->line("  3. Run <fg=green>php artisan migrate</> if you used crud:new.");
        $this->line("  4. Build frontend assets:");
        $this->line("       <fg=green>npm run dev</> (or npm run build)");
        $this->line("  5. Visit <fg=yellow>/{$lower}</> in your browser.");
        $this->newLine();
    }

    private function lcVersion(): string
    {
        return \Xslainadmin\LivewireCrud\LivewireCrud::VERSION;
    }

    // -----------------------------------------------------------------------
    // Banner
    // -----------------------------------------------------------------------

    private function renderBanner(): void
    {
        $this->newLine();
        $this->line("  <fg=blue;options=bold>Livewire CRUD Enterprise</> — Module Scaffold");
        $this->line("  <fg=gray>Scaffolding: </><fg=yellow>Modules/{$this->moduleName}</>");
        $this->newLine();
    }
}
