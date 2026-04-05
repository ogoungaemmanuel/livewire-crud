<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * MakeComponentCommand — generate a plain Livewire component and its blade view
 * inside a specified (or interactively selected) module.
 *
 * Usage:
 *   php artisan make:livewire-component Dashboard Shop
 *   php artisan make:livewire-component Admin/Settings Shop
 *   php artisan make:livewire-component Dashboard          # prompts for module
 *
 * Produces:
 *   Modules/{Module}/Livewire/{SubPath}/{Name}.php
 *   Modules/{Module}/resources/views/livewire/{sub/path/name}.blade.php
 */
final class MakeComponentCommand extends Command
{
    protected $signature = 'make:livewire-component
        {name              : Component name or sub-path (e.g. Dashboard, Admin/Settings)}
        {module?           : Module name in PascalCase (e.g. Shop); prompted if omitted}
        {--force           : Overwrite existing files without prompting}';

    protected $description = 'Generate a plain Livewire component and blade view in the selected module';

    public function __construct(private readonly Filesystem $files)
    {
        parent::__construct();
    }

    // -----------------------------------------------------------------------
    // Entry point
    // -----------------------------------------------------------------------

    public function handle(): int
    {
        $name   = $this->parseName();
        $module = $this->resolveModule();

        if ($module === null) {
            return self::FAILURE;
        }

        // ── Derive class parts ────────────────────────────────────────────
        $segments  = collect(explode('/', str_replace('\\', '/', $name)));
        $className = Str::studly($segments->last());
        $subDir    = $segments->slice(0, -1)->map(fn (string $p): string => Str::studly($p));

        // ── Target paths ──────────────────────────────────────────────────
        $componentPath = $this->componentPath($module, $subDir, $className);
        $viewPath      = $this->viewPath($module, $segments);

        // ── Build content ─────────────────────────────────────────────────
        $namespace = $this->buildNamespace($module, $subDir);
        $viewName  = $this->buildViewName($module, $segments);

        $componentContent = $this->buildComponent($namespace, $className, $viewName);
        $viewContent      = $this->buildView($className);

        // ── Write files ───────────────────────────────────────────────────
        $componentWritten = $this->writeFile($componentPath, $componentContent, "Component [{$className}]");
        $viewWritten      = $this->writeFile($viewPath, $viewContent, "View [{$viewName}]");

        if ($componentWritten || $viewWritten) {
            $this->newLine();
            $this->components->info('Plain Livewire component generated successfully.');
        }

        return self::SUCCESS;
    }

    // -----------------------------------------------------------------------
    // Input resolution
    // -----------------------------------------------------------------------

    private function parseName(): string
    {
        return trim((string) $this->argument('name'));
    }

    private function resolveModule(): ?string
    {
        $module = $this->argument('module');

        if ($module) {
            return trim((string) $module);
        }

        $available = $this->availableModules();

        if ($available->isEmpty()) {
            return (string) $this->ask('Module name (e.g. Shop)', 'Backend');
        }

        $choice = $this->choice(
            'Select the target module',
            $available->values()->all(),
            $available->search('Backend') !== false ? $available->search('Backend') : 0,
        );

        return (string) $choice;
    }

    /**
     * Scan base_path('Modules') for available module directories.
     *
     * @return Collection<int, string>
     */
    private function availableModules(): Collection
    {
        $modulesPath = base_path('Modules');

        if (! $this->files->isDirectory($modulesPath)) {
            return collect();
        }

        return collect($this->files->directories($modulesPath))
            ->map(fn (string $path): string => basename($path))
            ->sort()
            ->values();
    }

    // -----------------------------------------------------------------------
    // Path builders
    // -----------------------------------------------------------------------

    /**
     * Absolute path for the Livewire component class.
     *
     * @param  Collection<int, string>  $subDir
     */
    private function componentPath(string $module, Collection $subDir, string $className): string
    {
        $dir = base_path("Modules/{$module}/Livewire");

        if ($subDir->isNotEmpty()) {
            $dir .= '/' . $subDir->implode('/');
        }

        return "{$dir}/{$className}.php";
    }

    /**
     * Absolute path for the blade view.
     *
     * @param  Collection<int, string>  $segments
     */
    private function viewPath(string $module, Collection $segments): string
    {
        $slug = $segments->map(fn (string $p): string => Str::kebab($p))->implode('/');

        return base_path("Modules/{$module}/resources/views/livewire/{$slug}.blade.php");
    }

    // -----------------------------------------------------------------------
    // Content builders
    // -----------------------------------------------------------------------

    /**
     * Build the fully-qualified namespace for the component.
     *
     * @param  Collection<int, string>  $subDir
     */
    private function buildNamespace(string $module, Collection $subDir): string
    {
        $ns = "Modules\\{$module}\\Livewire";

        if ($subDir->isNotEmpty()) {
            $ns .= '\\' . $subDir->implode('\\');
        }

        return $ns;
    }

    /**
     * Build the view dot-notation name, e.g. shop::livewire.admin.settings
     *
     * @param  Collection<int, string>  $segments
     */
    private function buildViewName(string $module, Collection $segments): string
    {
        $slug = $segments->map(fn (string $p): string => Str::kebab($p))->implode('.');

        return Str::lower($module) . '::livewire.' . $slug;
    }

    private function buildComponent(string $namespace, string $className, string $viewName): string
    {
        return <<<PHP
<?php

namespace {$namespace};

use Livewire\Component;

class {$className} extends Component
{
    public function render()
    {
        return view('{$viewName}');
    }
}
PHP;
    }

    private function buildView(string $className): string
    {
        return <<<HTML
<div>
    {{-- {$className} --}}
</div>
HTML;
    }

    // -----------------------------------------------------------------------
    // File writing helper
    // -----------------------------------------------------------------------

    /**
     * Write a file, optionally prompting before overwriting.
     * Returns true if the file was actually written.
     */
    private function writeFile(string $path, string $content, string $label): bool
    {
        if ($this->files->exists($path) && ! $this->option('force')) {
            if (! $this->confirm("{$label} already exists. Overwrite?", false)) {
                $this->components->warn("Skipped: {$path}");
                return false;
            }
        }

        $this->files->makeDirectory(dirname($path), 0755, true, true);
        $this->files->put($path, $content);
        $this->components->twoColumnDetail($label, "<fg=green>{$path}</>");

        return true;
    }
}
