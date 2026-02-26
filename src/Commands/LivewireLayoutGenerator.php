<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

/**
 * crud:layout — Scaffold layout files for a Module.
 *
 * Generates:
 *   Layouts     app.blade.php        — Bootstrap 4 simple app layout
 *               backend.blade.php    — Full Metronic-style admin layout
 *               frontend.blade.php   — Centered panel / auth-style layout
 *   Partials    print.blade.php      — Print media CSS
 *               sidebar.blade.php    — Admin sidebar navigation
 *               topbar.blade.php     — Admin topbar / header
 *   Views       dashboard.blade.php  — Starter dashboard view
 *
 * Usage:
 *   php artisan crud:layout Shop
 *   php artisan crud:layout Shop --type=backend
 *   php artisan crud:layout Shop --force
 */
class LivewireLayoutGenerator extends Command
{
    protected $signature = 'crud:layout
        {module          : Module name (e.g. Shop)}
        {--type=all      : Layout type to generate: all | app | backend | frontend | partials | dashboard}
        {--force         : Overwrite files that already exist}';

    protected $description = 'Generate layout files (app, backend, frontend, sidebar, topbar, print, dashboard) for a Module';

    private Filesystem $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    // -----------------------------------------------------------------------
    // Handle
    // -----------------------------------------------------------------------

    public function handle(): int
    {
        $module      = trim((string) $this->argument('module'));
        $moduleLower = Str::lower($module);
        $type        = Str::lower(trim((string) $this->option('type')));
        $force       = (bool) $this->option('force');

        $this->info("Generating layout scaffolding for module: <comment>{$module}</comment>");
        if ($type !== 'all') {
            $this->line("  Type: <comment>{$type}</comment>");
        }
        $this->newLine();

        // Build replacement tokens
        $tokens = [
            '{{getModuleInputModule}}' => $module,
            '{{getModuleInput}}'       => $moduleLower,
        ];

        $stubBase = __DIR__ . '/../stubs/layouts';
        $viewBase = base_path("Modules/{$module}/resources/views/layouts");

        $generated = 0;

        // ── App layout ───────────────────────────────────────────────────────
        if (in_array($type, ['all', 'app'], true)) {
            $this->line('<options=bold>App layout</>');
            $this->writeFromStub(
                "{$stubBase}/app.blade.stub",
                "{$viewBase}/app.blade.php",
                $tokens,
                $force
            );
            $generated++;
        }

        // ── Backend layout ───────────────────────────────────────────────────
        if (in_array($type, ['all', 'backend'], true)) {
            $this->newLine();
            $this->line('<options=bold>Backend layout</>');
            $this->writeFromStub(
                "{$stubBase}/backend.blade.stub",
                "{$viewBase}/backend.blade.php",
                $tokens,
                $force
            );
            $generated++;
        }

        // ── Frontend layout ──────────────────────────────────────────────────
        if (in_array($type, ['all', 'frontend'], true)) {
            $this->newLine();
            $this->line('<options=bold>Frontend layout</>');
            $this->writeFromStub(
                "{$stubBase}/frontend.blade.stub",
                "{$viewBase}/frontend.blade.php",
                $tokens,
                $force
            );
            $generated++;
        }

        // ── Partials ─────────────────────────────────────────────────────────
        if (in_array($type, ['all', 'partials', 'backend'], true)) {
            $this->newLine();
            $this->line('<options=bold>Layout partials</>');

            $partials = [
                'print'   => 'print.blade.php',
                'sidebar' => 'sidebar.blade.php',
                'topbar'  => 'topbar.blade.php',
            ];

            foreach ($partials as $stub => $filename) {
                $this->writeFromStub(
                    "{$stubBase}/partials/{$stub}.blade.stub",
                    "{$viewBase}/{$filename}",
                    $tokens,
                    $force
                );
            }
            $generated++;
        }

        // ── Dashboard view ───────────────────────────────────────────────────
        if (in_array($type, ['all', 'dashboard'], true)) {
            $this->newLine();
            $this->line('<options=bold>Dashboard view</>');

            $dashBase = base_path("Modules/{$module}/resources/views");
            $this->writeFromStub(
                "{$stubBase}/dashboard.blade.stub",
                "{$dashBase}/dashboard.blade.php",
                $tokens,
                $force
            );
            $generated++;
        }

        if ($generated === 0) {
            $this->error("Unknown --type value: '{$type}'. Valid options: all, app, backend, frontend, partials, dashboard");
            return self::FAILURE;
        }

        // ── Summary ──────────────────────────────────────────────────────────
        $this->newLine();
        $this->info('Layout scaffolding generated successfully.');
        $this->newLine();
        $this->line('  <options=bold>Next steps</>');
        $this->line("  1. Extend layouts in your Blade views:  <comment>@extends('{$moduleLower}::layouts.backend')</comment>");
        $this->line("  2. Sections to define in child views:   <comment>@section('page_title', 'My Page')</comment>  and  <comment>@section('content')</comment>");
        $this->line("  3. Optional toolbar actions:            <comment>@section('toolbar_actions')</comment>");
        $this->line("  4. Include print partial in backend layout via:  <comment>@include('{$moduleLower}::layouts.print')</comment>");
        $this->line("  5. Register the module namespace in your Module's <comment>RouteServiceProvider</comment> / <comment>ViewServiceProvider</comment>.");

        return self::SUCCESS;
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /**
     * Read a stub, apply token replacements, and write to the destination path.
     */
    private function writeFromStub(
        string $stubPath,
        string $destPath,
        array  $tokens,
        bool   $force
    ): void {
        if (! $this->files->exists($stubPath)) {
            $this->warn("  Stub not found — skipped: {$stubPath}");
            return;
        }

        $alreadyExists = $this->files->exists($destPath);

        if (! $force && $alreadyExists) {
            $this->line("  <fg=yellow>EXISTS</> — {$this->relativePath($destPath)}");
            return;
        }

        $content = str_replace(
            array_keys($tokens),
            array_values($tokens),
            $this->files->get($stubPath)
        );

        $this->files->makeDirectory(dirname($destPath), 0755, true, true);
        $this->files->put($destPath, $content);

        $label = ($force && $alreadyExists) ? 'UPDATED' : 'CREATED';
        $this->line("  <fg=green>{$label}</> — {$this->relativePath($destPath)}");
    }

    /**
     * Return a workspace-relative path for cleaner terminal output.
     */
    private function relativePath(string $absolute): string
    {
        $base = rtrim(str_replace('\\', '/', base_path()), '/') . '/';
        $path = str_replace('\\', '/', $absolute);

        return str_starts_with($path, $base)
            ? substr($path, strlen($base))
            : $path;
    }
}
