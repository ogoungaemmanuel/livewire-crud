<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

/**
 * crud:auth — Scaffold full Livewire authentication for a Module.
 *
 * Generates:
 *   Livewire components  LoginComponent, RegisterComponent, ForgotPasswordComponent,
 *                        ResetPasswordComponent, VerifyEmailComponent,
 *                        ConfirmPasswordComponent, LogoutComponent
 *   Blade views          login, register, forgot-password, reset-password,
 *                        verify-email, confirm-password
 *   Auth layout          auth/layouts/auth.blade.php
 *   Routes file          routes/auth.php  (auto-required from web.php if hook present)
 *
 * Usage:
 *   php artisan crud:auth Shop
 *   php artisan crud:auth Shop --force   # overwrite existing files
 */
class LivewireAuthGenerator extends Command
{
    protected $signature = 'crud:auth
        {module          : Module name (e.g. Shop)}
        {--force         : Overwrite files that already exist}';

    protected $description = 'Generate Livewire authentication scaffolding (login, register, password reset, etc.) for a Module';

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
        $module      = trim($this->argument('module'));
        $moduleLower = Str::lower($module);
        $force       = (bool) $this->option('force');

        $this->info("Generating auth scaffolding for module: <comment>{$module}</comment>");
        $this->newLine();

        // Build replacement tokens
        $tokens = [
            '{{getModuleInputModule}}' => $module,
            '{{getModuleInput}}'       => $moduleLower,
        ];

        $stubBase = __DIR__ . '/../stubs/auth';
        $modBase  = base_path("Modules/{$module}");

        // ── Livewire components ──────────────────────────────────────────────
        $components = [
            'Login'           => 'LoginComponent',
            'Register'        => 'RegisterComponent',
            'ForgotPassword'  => 'ForgotPasswordComponent',
            'ResetPassword'   => 'ResetPasswordComponent',
            'VerifyEmail'     => 'VerifyEmailComponent',
            'ConfirmPassword' => 'ConfirmPasswordComponent',
            'Logout'          => 'LogoutComponent',
        ];

        foreach ($components as $class => $stubName) {
            $dest = "{$modBase}/Livewire/Auth/{$class}.php";
            $this->writeFromStub("{$stubBase}/{$stubName}.stub", $dest, $tokens, $force);
        }

        // ── Blade views ──────────────────────────────────────────────────────
        $views = [
            'login',
            'register',
            'forgot-password',
            'reset-password',
            'verify-email',
            'confirm-password',
        ];

        $viewBase = "{$modBase}/resources/views/auth";

        foreach ($views as $view) {
            $dest = "{$viewBase}/{$view}.blade.php";
            $this->writeFromStub("{$stubBase}/views/{$view}.stub", $dest, $tokens, $force);
        }

        // ── Auth layout ──────────────────────────────────────────────────────
        $this->writeFromStub(
            "{$stubBase}/layouts/auth.stub",
            "{$viewBase}/layouts/auth.blade.php",
            $tokens,
            $force
        );

        // ── Routes file ──────────────────────────────────────────────────────
        $routesDest = "{$modBase}/routes/auth.php";
        $this->writeFromStub("{$stubBase}/routes.stub", $routesDest, $tokens, $force);

        // Auto-require the auth routes from the module's web.php
        $this->injectAuthRequire($modBase, $module, $force);

        // ── Summary ──────────────────────────────────────────────────────────
        $this->newLine();
        $this->info('Auth scaffolding generated successfully.');
        $this->newLine();

        $this->line('  <options=bold>Next steps</>');
        $this->line("  1. Add <comment>require __DIR__ . '/auth.php';</comment> to <comment>Modules/{$module}/routes/web.php</comment> (done automatically if a <comment>//Auth Routes Hook</comment> comment is present).");
        $this->line("  2. Register the Livewire components in your Module's <comment>LivewireServiceProvider</comment> (or use Livewire auto-discovery).");
        $this->line("  3. Make sure <comment>APP_URL</comment> and your mail configuration are set for password reset emails.");
        $this->line("  4. Add a <comment>{{getModuleInput}}.dashboard</comment> named route if one doesn't exist yet.");

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

        if (! $force && $this->files->exists($destPath)) {
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

        $label = $force && $this->files->exists($destPath) ? 'UPDATED' : 'CREATED';
        $this->line("  <fg=green>{$label}</> — {$this->relativePath($destPath)}");
    }

    /**
     * Inject a `require __DIR__ . '/auth.php';` line into the module's web.php
     * if the file has the magic hook comment or doesn't already include auth.php.
     */
    private function injectAuthRequire(string $modBase, string $module, bool $force): void
    {
        $webPhp = "{$modBase}/routes/web.php";

        if (! $this->files->exists($webPhp)) {
            $this->warn("  web.php not found at {$webPhp} — skipping auto-require.");
            return;
        }

        $contents = $this->files->get($webPhp);
        $require  = "require __DIR__ . '/auth.php';";

        if (str_contains($contents, $require)) {
            return;  // already present
        }

        $hook = '//Auth Routes Hook';

        if (str_contains($contents, $hook)) {
            $contents = str_replace($hook, $hook . PHP_EOL . $require, $contents);
        } else {
            $contents .= PHP_EOL . $require . PHP_EOL;
        }

        $this->files->put($webPhp, $contents);
        $this->line("  <fg=green>UPDATED</> — {$this->relativePath($webPhp)} (auth.php required)");
    }

    /**
     * Strip the Laravel base_path prefix so messages stay readable.
     */
    private function relativePath(string $absolute): string
    {
        $base = str_replace('\\', '/', base_path()) . '/';
        return ltrim(str_replace('\\', '/', $absolute), '/');
    }
}
