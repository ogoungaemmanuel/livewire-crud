<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Xslainadmin\LivewireCrud\Data\GeneratorConfig;
use Xslainadmin\LivewireCrud\Enums\ThemeType;

/**
 * Interactive wizard that collects all generator options via prompts and
 * then delegates to the underlying `crud:generate` command.
 *
 * Usage:
 *   php artisan crud:make
 *   php artisan crud:make --table=users          # skip table prompt
 */
final class LivewireGenerateMakeCommand extends Command
{
    protected $signature = 'crud:make
                             {--table=      : Database table name (skips prompt)}
                             {--module=     : Module name (skips prompt)}
                             {--theme=      : Theme key (skips prompt)}
                             {--menu=admin  : Menu group key}
                             {--dry-run     : Preview config without generating files}';

    protected $description = 'Interactive wizard to scaffold a full enterprise Livewire CRUD module';

    public function handle(): int
    {
        $this->renderBanner();

        // -----------------------------------------------------------------------
        // Collect inputs (use CLI option if supplied, otherwise prompt)
        // -----------------------------------------------------------------------

        $table = $this->resolveTable();
        if ($table === null) {
            return self::FAILURE;
        }

        $module = $this->resolveModule();
        $theme  = $this->resolveTheme();
        $menu   = (string) ($this->option('menu') ?: $this->ask('Menu group (admin / super_admin)', 'admin'));

        // -----------------------------------------------------------------------
        // Feature toggles
        // -----------------------------------------------------------------------

        $this->newLine();
        $this->components->info('Select optional features to generate:');

        $features = $this->choice(
            'Which artefacts should be generated? (comma-separated numbers)',
            [
                'Export (Excel / CSV / PDF)',
                'Import (Excel / CSV)',
                'Charts & Analytics',
                'FullCalendar integration',
                'Notification class',
                'Email / Mailable',
                'Print / PDF export',
                'File upload controller',
                'Model factory',
            ],
            '0,1,2,3,4,5,6,7,8',
            multiple: true,
        );

        // Map choices back to flags
        $featureMap = [
            'Export (Excel / CSV / PDF)'  => 'generateExport',
            'Import (Excel / CSV)'        => 'generateImport',
            'Charts & Analytics'          => 'generateChart',
            'FullCalendar integration'    => 'generateCalendar',
            'Notification class'          => 'generateNotification',
            'Email / Mailable'            => 'generateEmail',
            'Print / PDF export'          => 'generatePrint',
            'File upload controller'      => 'generateUpload',
            'Model factory'               => 'generateFactory',
        ];

        $selected = is_array($features) ? $features : [$features];

        // -----------------------------------------------------------------------
        // Model feature toggles
        // -----------------------------------------------------------------------

        $this->newLine();
        $this->components->info('Model feature flags:');

        $withSoftDeletes  = $this->confirm('Add SoftDeletes?', true);
        $withActivityLog  = $this->confirm('Enable Spatie activity-log?', true);
        $withScoutSearch  = $this->confirm('Enable Laravel Scout search?', false);
        $withQueryBuilder = $this->confirm('Enable Spatie query-builder?', true);

        // -----------------------------------------------------------------------
        // Build config DTO and display summary
        // -----------------------------------------------------------------------

        $config = GeneratorConfig::fromArray([
            'table_name'           => $table,
            'module'               => $module,
            'theme'                => $theme,
            'menu'                 => $menu,
            'model_namespace'      => config('livewire-crud.model.namespace', 'App\\Models'),
            'generate_export'      => in_array('Export (Excel / CSV / PDF)', $selected, true),
            'generate_import'      => in_array('Import (Excel / CSV)', $selected, true),
            'generate_chart'       => in_array('Charts & Analytics', $selected, true),
            'generate_calendar'    => in_array('FullCalendar integration', $selected, true),
            'generate_notification'=> in_array('Notification class', $selected, true),
            'generate_email'       => in_array('Email / Mailable', $selected, true),
            'generate_print'       => in_array('Print / PDF export', $selected, true),
            'generate_pdf_export'  => in_array('Print / PDF export', $selected, true),
            'generate_upload'      => in_array('File upload controller', $selected, true),
            'generate_factory'     => in_array('Model factory', $selected, true),
            'with_soft_deletes'    => $withSoftDeletes,
            'with_activity_log'    => $withActivityLog,
            'with_scout_search'    => $withScoutSearch,
            'with_query_builder'   => $withQueryBuilder,
        ]);

        $this->renderSummary($config);

        if ($this->option('dry-run')) {
            $this->components->warn('Dry-run mode — no files were written.');
            return self::SUCCESS;
        }

        if (! $this->confirm('Generate CRUD files now?', true)) {
            $this->components->warn('Aborted — no files written.');
            return self::SUCCESS;
        }

        // -----------------------------------------------------------------------
        // Delegate to `crud:generate`
        // -----------------------------------------------------------------------

        $this->newLine();
        $exitCode = $this->call('crud:generate', [
            'name'   => $config->tableName,
            'theme'  => $config->themeValue(),
            'menu'   => $config->menu,
            'module' => $config->module,
        ]);

        if ($exitCode === self::SUCCESS) {
            $this->newLine();
            $this->components->success(
                "CRUD module [{$config->module}::{$config->tableName}] generated successfully."
            );
        }

        return $exitCode;
    }

    // -----------------------------------------------------------------------
    // Private helpers
    // -----------------------------------------------------------------------

    private function resolveTable(): ?string
    {
        $table = (string) ($this->option('table') ?: $this->ask('Database table name'));

        if (empty($table)) {
            $this->components->error('Table name is required.');
            return null;
        }

        if (! Schema::hasTable($table)) {
            $this->components->error("Table [{$table}] does not exist in the database.");
            return null;
        }

        return $table;
    }

    private function resolveModule(): string
    {
        return (string) ($this->option('module') ?: $this->ask('Module name (e.g. Inventory)', 'Backend'));
    }

    private function resolveTheme(): string
    {
        if ($option = $this->option('theme')) {
            return (string) $option;
        }

        $options = ThemeType::options();

        $label = $this->choice(
            'Select UI theme',
            array_values($options),
            ThemeType::Default->label(),
        );

        return (string) array_search($label, $options, true);
    }

    private function renderBanner(): void
    {
        $version = \Xslainadmin\LivewireCrud\LivewireCrud::VERSION;
        $this->newLine();
        $this->line("  <fg=blue;options=bold>Livewire CRUD Enterprise Generator</> <fg=gray>v{$version}</>");
        $this->line('  <fg=gray>Bootstrap 5 · Alpine.js · Livewire 3 · Laravel 12</>');
        $this->newLine();
    }

    private function renderSummary(GeneratorConfig $config): void
    {
        $this->newLine();
        $this->components->info('Generation summary:');

        $this->table(
            ['Setting', 'Value'],
            [
                ['Table',        $config->tableName],
                ['Module',       $config->module],
                ['Theme',        $config->theme->label()],
                ['Menu',         $config->menu],
                ['Model NS',     $config->modelNamespace],
                ['Soft Deletes', $config->withSoftDeletes  ? '<fg=green>yes</>' : '<fg=gray>no</>'],
                ['Activity Log', $config->withActivityLog  ? '<fg=green>yes</>' : '<fg=gray>no</>'],
                ['Scout Search', $config->withScoutSearch  ? '<fg=green>yes</>' : '<fg=gray>no</>'],
                ['Query Builder',$config->withQueryBuilder ? '<fg=green>yes</>' : '<fg=gray>no</>'],
            ]
        );
        $this->newLine();
    }
}
