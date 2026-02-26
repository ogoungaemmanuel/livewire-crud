<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

/**
 * Enterprise install command for the Livewire CRUD package.
 *
 * Modes
 * ─────
 * Traditional  (default)  — scaffolds inside app/ + resources/  (legacy behaviour)
 * Modular      (--modular) — scaffolds inside Modules/{Module}/  (nWidart/laravel-modules)
 *
 * Modular mode extras
 * ───────────────────
 *   • Detects / creates the target module via `module:make`
 *   • Creates all required sub-directories inside Modules/{Module}/
 *   • Injects route + nav hooks into the module's own web.php
 *   • Optionally runs crud:layout {Module} and crud:auth {Module}
 *   • Registers the module in config/app.php providers array (fallback hint if auto-discovery is off)
 */
final class LivewireInstall extends Command
{
    protected Filesystem $filesystem;
    protected string $stubDir;

    /** @var array<string, string> */
    private array $replaces = [];

    protected $signature = 'crud:install
                             {--module=      : Module name for modular mode (implies --modular)}
                             {--modular      : Scaffold inside a nWidart module instead of app/}
                             {--skip-auth    : Skip auth scaffolding}
                             {--skip-layout  : Skip layout scaffolding (modular mode only)}
                             {--skip-npm     : Skip npm install and build}
                             {--force        : Overwrite existing files without prompting}';

    protected $description = 'Install the Livewire CRUD Enterprise package — supports traditional and modular (nWidart) modes';

    // -----------------------------------------------------------------------
    // Entry point
    // -----------------------------------------------------------------------

    public function handle(): int
    {
        $this->filesystem = new Filesystem();
        $this->renderBanner();

        if (! $this->option('force') && ! $this->confirm(
            'This will scaffold files and insert route/nav hooks. Continue?', true
        )) {
            $this->components->warn('Installation aborted — no files were modified.');
            return self::SUCCESS;
        }

        $modular = $this->option('modular') || $this->option('module') !== null;

        return $modular ? $this->handleModular() : $this->handleTraditional();
    }

    // -----------------------------------------------------------------------
    // Traditional mode
    // -----------------------------------------------------------------------

    private function handleTraditional(): int
    {
        $this->line('  Mode: <fg=cyan>Traditional</> (app/ + resources/)');
        $this->newLine();

        // 1. Directories
        $this->components->task('Creating core directories', function (): void {
            $this->filesystem->ensureDirectoryExists(app_path('Http/Livewire'));
            $this->filesystem->ensureDirectoryExists(app_path('Http/Controllers'));
            $this->filesystem->ensureDirectoryExists(app_path('Models'));
            $this->filesystem->ensureDirectoryExists(resource_path('views/livewire'));
            $this->filesystem->ensureDirectoryExists(resource_path('views/layouts'));
        });

        // 2. Optional auth
        if (! $this->option('skip-auth') && $this->confirm('Scaffold Laravel authentication (ui:auth)?', false)) {
            $this->components->task('Scaffolding authentication', function (): void {
                Artisan::call('ui:auth', [], $this->getOutput());
            });
        }

        // 3. Route hooks
        $this->components->task('Injecting route hooks', function (): void {
            $this->injectRouteHook(base_path('routes/web.php'));
        });

        // 4. Publish config
        $this->components->task('Publishing package configuration', function (): void {
            $this->publishConfig();
        });

        // 5. Frontend stubs
        $this->stubDir = __DIR__ . '/../../resources/install';
        if ($this->filesystem->isDirectory($this->stubDir)) {
            $this->components->task('Copying frontend scaffold files', fn () => $this->generateFiles());
        } else {
            $this->components->warn('No frontend stubs directory found — skipping asset scaffold.');
        }

        // 6. npm
        $this->runNpm();

        // 7. Summary
        $this->newLine();
        $this->components->success('Livewire CRUD Enterprise installed successfully!');
        $this->newLine();
        $this->line('  <options=bold>Next steps:</>');
        $this->line('  1. Run <fg=green>php artisan crud:make</> to scaffold your first CRUD module interactively.');
        $this->line('  2. Or: <fg=green>php artisan crud:generate {table} {theme} {menu} {module}</>');
        $this->line('  3. Configure <fg=yellow>config/livewire-crud.php</>');
        $this->line('  4. Review <fg=yellow>routes/web.php</> for the injected route hook.');
        $this->newLine();

        return self::SUCCESS;
    }

    // -----------------------------------------------------------------------
    // Modular mode
    // -----------------------------------------------------------------------

    private function handleModular(): int
    {
        // ── Resolve module name ──────────────────────────────────────────────
        $module = $this->option('module')
            ?? $this->ask('Module name (PascalCase, e.g. Backend)', 'Backend');

        $module      = trim((string) $module);
        $moduleLower = Str::lower($module);
        $modBase     = base_path("Modules/{$module}");

        // ── nWidart presence check ───────────────────────────────────────────
        if (! class_exists(\Nwidart\Modules\LaravelModulesServiceProvider::class)) {
            $this->components->warn(
                'nwidart/laravel-modules is not installed. ' .
                'Run: composer require nwidart/laravel-modules'
            );
            if (! $this->confirm('Continue anyway (manual module structure)?', false)) {
                return self::FAILURE;
            }
        }

        $this->line("  Mode: <fg=cyan>Modular</> → <fg=yellow>Modules/{$module}</>");
        $this->newLine();

        // ── 1. Create / ensure the module exists ─────────────────────────────
        $this->components->task("Creating module: {$module}", function () use ($module, $modBase): void {
            if ($this->artisanCommandExists('module:make') && ! is_dir($modBase)) {
                Artisan::call('module:make', ['name' => [$module]], $this->getOutput());
            } else {
                // Manual skeleton
                foreach ([
                    'Http/Livewire',
                    'Http/Controllers',
                    'Models',
                    'Database/factories',
                    'Database/migrations',
                    'resources/views/livewire',
                    'resources/views/layouts',
                    'resources/views/auth',
                    'resources/css',
                    'resources/js',
                    'routes',
                    'Livewire',
                    'Providers',
                ] as $dir) {
                    $this->filesystem->ensureDirectoryExists("{$modBase}/{$dir}");
                }
            }
        });

        // ── 2. Ensure all needed sub-directories exist ───────────────────────
        $this->components->task('Ensuring module sub-directories', function () use ($modBase): void {
            foreach ([
                'Http/Livewire',
                'Http/Controllers',
                'Models',
                'Database/factories',
                'Database/migrations',
                'Livewire',
                'Livewire/Auth',
                'Exports',
                'Imports',
                'Notifications',
                'Emails',
                'Charts',
                'Providers',
                'resources/views/livewire',
                'resources/views/layouts',
                'resources/views/auth',
                'resources/css',
                'resources/js',
                'routes',
            ] as $dir) {
                $this->filesystem->ensureDirectoryExists("{$modBase}/{$dir}");
            }
        });

        // ── 3. Scaffold web.php if missing ───────────────────────────────────
        $this->components->task('Scaffolding route file', function () use ($modBase, $module, $moduleLower): void {
            $webPhp = "{$modBase}/routes/web.php";
            if (! $this->filesystem->exists($webPhp)) {
                $this->filesystem->put($webPhp, <<<PHP
<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| {$module} Module Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['web', 'auth'])->prefix('{$moduleLower}')->name('{$moduleLower}.')->group(function () {
    //Route Hooks - Do not delete//
});

PHP);
                $this->line("  <fg=green>CREATED</> — Modules/{$module}/routes/web.php");
            } else {
                $this->injectRouteHook($webPhp);
            }
        });

        // ── 4. Scaffold module.json if missing ───────────────────────────────
        $this->components->task('Scaffolding module.json', function () use ($modBase, $module, $moduleLower): void {
            $moduleJson = "{$modBase}/module.json";
            if (! $this->filesystem->exists($moduleJson)) {
                $this->filesystem->put($moduleJson, json_encode([
                    'name'        => $module,
                    'alias'       => $moduleLower,
                    'description' => "{$module} module — generated by Livewire CRUD Enterprise",
                    'keywords'    => [],
                    'priority'    => 0,
                    'providers'   => ["Modules\\{$module}\\Providers\\{$module}ServiceProvider"],
                    'aliases'     => [],
                    'files'       => [],
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
                $this->line("  <fg=green>CREATED</> — Modules/{$module}/module.json");
            }
        });

        // ── 5. Scaffold ServiceProvider if missing ───────────────────────────
        $this->components->task('Scaffolding ServiceProvider', function () use ($modBase, $module, $moduleLower): void {
            $providerPath = "{$modBase}/Providers/{$module}ServiceProvider.php";
            if (! $this->filesystem->exists($providerPath)) {
                $this->filesystem->put($providerPath, <<<PHP
<?php

declare(strict_types=1);

namespace Modules\\{$module}\\Providers;

use Illuminate\Support\ServiceProvider;

class {$module}ServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        \$this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        \$this->loadViewsFrom(__DIR__ . '/../resources/views', '{$moduleLower}');
        \$this->loadMigrationsFrom(__DIR__ . '/../Database/migrations');
    }

    public function register(): void
    {
        //
    }
}

PHP);
                $this->line("  <fg=green>CREATED</> — Modules/{$module}/Providers/{$module}ServiceProvider.php");
            }
        });

        // ── 6. Optional: inject module into bootstrap/providers.php or advise ─
        $this->components->task('Registering module provider', function () use ($module): void {
            $bootstrapProviders = base_path('bootstrap/providers.php');
            $providerClass      = "Modules\\{$module}\\Providers\\{$module}ServiceProvider::class";

            if ($this->filesystem->exists($bootstrapProviders)) {
                $content = $this->filesystem->get($bootstrapProviders);
                if (! str_contains($content, "Modules\\{$module}")) {
                    // Insert before the closing ];
                    $content = str_replace(
                        '];',
                        "    {$providerClass},\n];",
                        $content
                    );
                    $this->filesystem->put($bootstrapProviders, $content);
                    $this->line("  <fg=green>UPDATED</> — bootstrap/providers.php");
                } else {
                    $this->line("  <fg=yellow>EXISTS</> — already registered in bootstrap/providers.php");
                }
            } else {
                $this->line("  <fg=yellow>NOTICE</> — Add <comment>{$providerClass}</comment> to config/app.php providers array.");
            }
        });

        // ── 7. Publish package config ────────────────────────────────────────
        $this->components->task('Publishing package configuration', function (): void {
            $this->publishConfig();
        });

        // ── 8. crud:layout ──────────────────────────────────────────────────
        if (! $this->option('skip-layout')) {
            $runLayout = $this->confirm("Run <fg=green>crud:layout {$module}</>?", true);
            if ($runLayout) {
                $this->components->task("Generating layouts for {$module}", function () use ($module): void {
                    Artisan::call('crud:layout', [
                        'module'  => $module,
                        '--force' => (bool) $this->option('force'),
                    ], $this->getOutput());
                });
            }
        }

        // ── 9. crud:auth ─────────────────────────────────────────────────────
        if (! $this->option('skip-auth')) {
            $runAuth = $this->confirm("Run <fg=green>crud:auth {$module}</>?", true);
            if ($runAuth) {
                $this->components->task("Generating auth scaffolding for {$module}", function () use ($module): void {
                    Artisan::call('crud:auth', [
                        'module'  => $module,
                        '--force' => (bool) $this->option('force'),
                    ], $this->getOutput());
                });
            }
        }

        // ── 10. npm ──────────────────────────────────────────────────────────
        $this->runNpm();

        // ── Summary ──────────────────────────────────────────────────────────
        $this->newLine();
        $this->components->success("Module [{$module}] installed successfully!");
        $this->newLine();
        $this->line('  <options=bold>Next steps:</>');
        $this->line("  1. <fg=green>php artisan crud:generate {table} modern admin {$module}</>");
        $this->line("  2. <fg=green>php artisan crud:new {table} modern admin {$module}</> — code-first (no DB table needed)");
        $this->line("  3. Add the module route in <fg=yellow>Modules/{$module}/routes/web.php</> if it is not auto-loaded.");
        $this->line("  4. Extend layouts in child views: <fg=yellow>@extends('{$moduleLower}::layouts.backend')</>");
        $this->line('  5. Configure <fg=yellow>config/livewire-crud.php</> for environment-specific settings.');
        $this->newLine();

        return self::SUCCESS;
    }

    // -----------------------------------------------------------------------
    // Shared helpers
    // -----------------------------------------------------------------------

    /**
     * Append the standard route hook to a web.php file if not already present.
     */
    private function injectRouteHook(string $routeFile): void
    {
        $hook = '//Route Hooks - Do not delete//';

        if ($this->filesystem->exists($routeFile)) {
            $content = $this->filesystem->get($routeFile);
            if (! str_contains($content, $hook)) {
                $this->filesystem->append($routeFile, "\n{$hook}\n");
                $this->line("  <fg=green>UPDATED</> — " . ltrim(str_replace(base_path(), '', $routeFile), '\\/'));
            }
        }
    }

    private function publishConfig(): void
    {
        Artisan::call('vendor:publish', [
            '--provider' => 'Xslainadmin\\LivewireCrud\\LivewireCrudServiceProvider',
            '--tag'      => 'livewire-crud-config',
            '--force'    => (bool) $this->option('force'),
        ], $this->getOutput());
    }

    private function runNpm(): void
    {
        if ($this->option('skip-npm')) {
            return;
        }

        if (! $this->confirm('Run npm install && npm run build?', false)) {
            return;
        }

        $this->components->task('Installing npm dependencies and building assets', function (): void {
            $this->newLine();
            exec('npm install && npm run build', $output, $exitCode);
            foreach ($output as $line) {
                $this->line("  {$line}");
            }
        });
    }

    /**
     * Check whether an Artisan command is registered.
     */
    private function artisanCommandExists(string $name): bool
    {
        return $this->getApplication()->has($name);
    }

    // -----------------------------------------------------------------------
    // Frontend stubs (traditional mode)
    // -----------------------------------------------------------------------

    public function generateFiles(): void
    {
        foreach ($this->filesystem->allFiles($this->stubDir, true) as $file) {
            $filePath = $this->replace(Str::replaceLast('.stub', '', $file->getRelativePathname()));
            $fileDir  = $this->replace($file->getRelativePath());

            if ($fileDir) {
                $this->filesystem->ensureDirectoryExists($fileDir);
            }

            $this->filesystem->put($filePath, $this->replace($file->getContents()));
            $this->line("  Generated: <fg=green>{$filePath}</>");
        }
    }

    private function replace(string $content): string
    {
        foreach ($this->replaces as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }

        return $content;
    }

    // -----------------------------------------------------------------------
    // Banner
    // -----------------------------------------------------------------------

    private function renderBanner(): void
    {
        $version = \Xslainadmin\LivewireCrud\LivewireCrud::VERSION;
        $this->newLine();
        $this->line("  <fg=blue;options=bold>Livewire CRUD Enterprise</> <fg=gray>v{$version} — Installation Wizard</>");
        $this->line('  <fg=gray>Bootstrap 5 · Alpine.js · Livewire 3 · Laravel 12</>');
        $this->newLine();
    }
}

