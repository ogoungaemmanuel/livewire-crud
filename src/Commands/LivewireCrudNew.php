<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Commands;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Xslainadmin\LivewireCrud\Support\StubTokens;

/**
 * crud:new — Generate full Livewire CRUD + migration for a table that does NOT yet exist in the DB.
 *
 * The developer defines columns interactively. The command produces:
 *   • Database migration  (Modules/{Module}/Database/Migrations/…_create_{table}_table.php)
 *   • Eloquent model      (Modules/{Module}/Models/{Model}.php)
 *   • Livewire component  (Modules/{Module}/Livewire/{Models}.php)
 *   • All blade views     (Modules/{Module}/resources/views/livewire/{table}/*.blade.php)
 *   • Import / Export / Factory / Notification / Email / Chart / etc.
 *   • Route entry + Nav link (same hooks as crud:generate)
 *
 * Usage:
 *   php artisan crud:new products modern admin Shop
 */
class LivewireCrudNew extends LivewireGeneratorCommand
{
    protected Filesystem $filesystem;
    protected string     $argument = '';

    /** @var array<string, string> */
    private array $replaces = [];

    protected $signature = 'crud:new
        {name    : Snake-case table name (e.g. products)}
        {theme   : Theme slug (none|nonedefault|modern|default)}
        {menu    : Menu file name under Modules/backend/resources/views/pmsmenu/}
        {module  : Module name (e.g. Shop)}
        {--sfc   : Generate a Livewire Volt single-file component (requires livewire/volt)}';

    protected $description = 'Generate Livewire CRUD + migration for a NEW table (not yet in the database)';

    // -----------------------------------------------------------------------
    // Column type choices presented to the developer
    // Maps friendly label → raw DB type string recognised by TypeMapper
    // -----------------------------------------------------------------------

    /** @var array<string, string> */
    private const COLUMN_TYPES = [
        'string (varchar 255)'       => 'varchar(255)',
        'string (varchar 100)'       => 'varchar(100)',
        'string (varchar 50)'        => 'varchar(50)',
        'text'                       => 'text',
        'longText'                   => 'longtext',
        'integer'                    => 'integer',
        'bigInteger'                 => 'bigint',
        'unsignedBigInteger (FK _id)'=> 'unsignedBigInteger',
        'tinyInteger / boolean'      => 'tinyint(1)',
        'decimal (8,2)'              => 'decimal(8,2)',
        'decimal (15,4)'             => 'decimal(15,4)',
        'float'                      => 'float',
        'double'                     => 'double',
        'date'                       => 'date',
        'dateTime'                   => 'datetime',
        'timestamp'                  => 'timestamp',
        'time'                       => 'time',
        'enum'                       => 'enum',
        'json'                       => 'json',
        'uuid'                       => 'uuid',
    ];

    // -----------------------------------------------------------------------
    // handle()
    // -----------------------------------------------------------------------

    /** @throws \Illuminate\Contracts\Filesystem\FileNotFoundException */
    public function handle(): int
    {
        $this->table  = $this->getNameInput();
        $this->module = $this->getModuleInput();
        $this->theme  = $this->getThemeInput();
        $this->menu   = $this->getMenuInput();

        $this->info("Creating NEW CRUD for table: <comment>{$this->table}</comment>");
        $this->info('The table does not need to exist yet — a migration will be created.');
        $this->newLine();

        // ── Collect columns interactively ──────────────────────────────────
        $columns = $this->collectColumns();

        if (empty($columns)) {
            $this->error('No columns defined. Aborting.');
            return self::FAILURE;
        }

        // Inject the collected columns so getColumns() never hits the DB
        $this->setColumns($columns);

        // Build the class name from table name
        $this->modelName = Str::studly(Str::singular($this->table));

        // ── Generate everything ────────────────────────────────────────────
        $this->buildMigration()
             ->buildModel()
             ->buildViews();

        // ── Route injection ────────────────────────────────────────────────
        $this->injectRoute();

        // ── Nav link injection ─────────────────────────────────────────────
        $this->injectNavLink();

        $this->newLine();
        $this->info('Livewire Component & CRUD Generated Successfully.');
        $this->comment('Run <info>php artisan migrate</info> to create the table.');

        return self::SUCCESS;
    }

    // -----------------------------------------------------------------------
    // Required abstract implementations (file generation is all in parent)
    // -----------------------------------------------------------------------

    /** {@inheritdoc} */
    public function buildModel(): static
    {
        // Re-use the full implementation from LivewireCrudGenerator
        // ── resolve all paths ───────────────────────────────────────────────
        $module              = $this->getModuleInput();
        $theme               = $this->getThemeInput();
        $modelPath           = $this->_getCreateModelPath($this->modelName);
        $importPath          = $this->_getImportPath($this->modelName);
        $exportPath          = $this->_getExportPath($this->modelName);
        $notificationPath    = $this->_getNotificationPath($this->modelName);
        $emailPath           = $this->_getEmailPath($this->modelName);
        $chartPath           = $this->_getChartPath($this->modelName);
        $uploadPath          = $this->_getUploadPath($this->modelName);
        $fullcalendarPath    = $this->_getFullcalendarPath($this->modelName);
        $printPath           = $this->_getPrintPath($this->modelName);
        $pdfexportPath       = $this->_getPdfExportPath($this->modelName);
        $modulePath          = trim($this->_getModulePath($this->modelName), '{}');
        $factoryPath         = $this->_getFactoryPath($this->modelName);

        if ($this->files->exists($modulePath)
            && $this->ask('Livewire Component ' . Str::studly(Str::singular($this->table)) . 'Component already exists. Overwrite? (y/n)', 'y') === 'n') {
            return $this;
        }

        $replace = array_merge($this->buildReplacements(), $this->modelReplacements());

        if ($this->option('sfc')) {
            $voltTemplate = str_replace(
                array_keys($replace), array_values($replace),
                $this->getStub('LivewireVolt')
            );
            $this->warn('Creating: <info>Livewire Volt Single-File Component...</info>');
            $this->write($this->_getVoltComponentPath(), $voltTemplate);
            $this->info('  <fg=yellow>Note:</> Add <comment>Volt::mount()</comment> in your module service provider to register the component.');
        } else {
            $this->warn('Creating: <info>Livewire Component...</info>');
            $livewireTemplate = str_replace(
                array_keys($replace), array_values($replace),
                $this->getStub($theme === 'none' ? 'Livewire' : 'Livewirethemed')
            );
            $this->write($modulePath, $livewireTemplate);
        }

        $this->warn('Creating: <info>Model...</info>');
        $this->write($modelPath, str_replace(array_keys($replace), array_values($replace), $this->getStub('Model')));

        $this->warn('Creating: <info>Factory...</info>');
        $this->write($factoryPath, str_replace(array_keys($replace), array_values($replace), $this->getStub('Factory')));

        $this->warn('Creating: <info>Import...</info>');
        $this->write($importPath, str_replace(array_keys($replace), array_values($replace), $this->getStub('Import')));

        $this->warn('Creating: <info>Export...</info>');
        $this->write($exportPath, str_replace(array_keys($replace), array_values($replace), $this->getStub('Export')));

        $this->warn('Creating: <info>Notification...</info>');
        $this->write($notificationPath, str_replace(array_keys($replace), array_values($replace), $this->getStub('Notification')));

        $this->warn('Creating: <info>Email...</info>');
        $this->write($emailPath, str_replace(array_keys($replace), array_values($replace), $this->getStub('Email')));

        $this->warn('Creating: <info>Chart...</info>');
        $this->write($chartPath, str_replace(array_keys($replace), array_values($replace), $this->getStub('Chart')));

        $this->warn('Creating: <info>Fullcalendar...</info>');
        $this->write($fullcalendarPath, str_replace(array_keys($replace), array_values($replace), $this->getStub('Fullcalendar')));

        $this->warn('Creating: <info>Print...</info>');
        $this->write($printPath, str_replace(array_keys($replace), array_values($replace), $this->getStub('Print')));

        $this->warn('Creating: <info>Pdf Export...</info>');
        $this->write($pdfexportPath, str_replace(array_keys($replace), array_values($replace), $this->getStub('PdfExport')));

        $this->warn('Creating: <info>Upload...</info>');
        $this->write($uploadPath, str_replace(array_keys($replace), array_values($replace), $this->getStub('Upload')));

        // ── Resource ───────────────────────────────────────────────────────
        $this->warn('Creating: <info>Resource class...</info>');
        $this->write(
            $this->_getResourcePath(),
            str_replace(array_keys($replace), array_values($replace), $this->getStub('Resource'))
        );

        return $this;
    }

    /** {@inheritdoc} */
    public function buildViews(): static
    {
        if ($this->option('sfc')) {
            $this->info('Skipping separate view files (--sfc mode: view is embedded in the Volt component).');
            return $this;
        }

        $theme = $this->getThemeInput();
        $this->warn('Creating: <info>Views...</info>');

        $tableHead  = "\n";
        $tableBody  = "\n";
        $viewRows   = "\n";
        $show       = "\n";
        $showfields = "\n";
        $type       = 'text';
        $formdata   = "\n";

        $columnTypes = [];
        $columns     = $this->getColumns();

        foreach ($columns as $column) {
            if (!isset($column->Field, $column->Type)) {
                continue;
            }
            $columnTypes[$column->Field] = $this->typeMapper()->htmlInputType($column->Field, $column->Type);
        }

        foreach ($this->getFilteredColumns() as $columnName) {
            $title = Str::title(str_replace('_', ' ', $columnName));

            $columnObject = null;
            foreach ($columns as $col) {
                if ($col->Field === $columnName) {
                    $columnObject = $col;
                    break;
                }
            }

            $datatype     = $columnObject ? $columnObject->Type : 'text';
            $columnDatatype = $this->typeMapper()->htmlInputType($columnName, $datatype);
            $columnType   = $columnTypes[$columnName] ?? 'text';

            $tableHead  .= "\t\t\t\t<th>{$title}</th>\n";
            $tableBody  .= "\t\t\t\t<td>{{ \$row->{$columnName} }}</td>\n";

            $formdata   .= $this->getDataField($title, $columnName, $columnDatatype) . "\n";
            $show       .= $this->showField($title, $columnName, 'show-field') . "\n";
            $showfields .= $this->showField($title, $columnName, 'show-field') . "\n";
            $viewRows   .= $this->getField($title, $columnName, 'view-row', $columnType) . "\n";
        }

        $replace = array_merge($this->buildReplacements(), [
            StubTokens::TABLE_HEADER              => $tableHead,
            StubTokens::TABLE_BODY                => $tableBody,
            StubTokens::VIEW_ROWS                 => $viewRows,
            StubTokens::FORM                      => $formdata,
            StubTokens::SHOW                      => $show,
            StubTokens::TYPE                      => $type,
            StubTokens::SHOW_FIELDS               => $showfields,
            StubTokens::THEME_LOWER               => Str::lower($this->getThemeInput()),
            StubTokens::GET_MODULE_INPUT_CLASS    => Str::studly($this->getModuleInput()),
            StubTokens::GET_MODULE_INPUT_MODULE   => $this->getModuleInput(),
            StubTokens::GET_MODULE_INPUT_LOWER    => Str::lower($this->getModuleInput()),
            StubTokens::GET_THEME_INPUT           => $this->getThemeInput(),
            StubTokens::GET_THEME_INPUT_LOWER     => Str::lower($this->getThemeInput()),
            StubTokens::GET_NAME_INPUT            => $this->getNameInput(),
            StubTokens::GET_NAME_INPUT_LOWER      => Str::lower($this->getNameInput()),
            StubTokens::GET_NAME_INPUT_PLURAL_LOWER => Str::lower(Str::plural($this->getNameInput())),
        ]);

        $this->buildLayout();

        foreach (['view', 'index', 'create', 'delete', 'show', 'import', 'update', 'pdf-export', 'print', 'mobile_index'] as $view) {
            if ($theme === 'none') {
                $stubKey = "views/{$view}";
            } elseif ($theme === 'nonedefault') {
                $stubKey = "viewsdefault/{$view}";
            } else {
                $stubKey = "themes/{$theme}/views/{$view}";
            }

            $viewTemplate = str_replace(array_keys($replace), array_values($replace), $this->getStub($stubKey));
            $this->write($this->_getViewPath($view), $viewTemplate);
        }

        return $this;
    }

    // -----------------------------------------------------------------------
    // Interactive column builder
    // -----------------------------------------------------------------------

    /**
     * Loop asking the developer to define each column.
     *
     * @return array<object>  Column descriptor objects (same shape as SHOW COLUMNS rows)
     */
    private function collectColumns(): array
    {
        $this->line('<options=bold>Define table columns</> (leave column name blank to finish)');
        $this->line('Columns <comment>id</comment>, <comment>created_at</comment>, and <comment>updated_at</comment> are added automatically.');
        $this->newLine();

        $columns = [];
        $index   = 1;

        while (true) {
            $field = $this->ask("  Column #{$index} name (blank to finish)");

            if ($field === null || trim($field) === '') {
                break;
            }

            $field = Str::snake(trim($field));

            // Guard against duplicates
            $existing = array_column($columns, 'Field');
            if (in_array($field, $existing, true)) {
                $this->warn("  Column '{$field}' already defined — skipping.");
                continue;
            }

            // ── Type ───────────────────────────────────────────────────────
            $typeLabels = array_keys(self::COLUMN_TYPES);
            $typeChoice = $this->choice("  Type for <comment>{$field}</comment>", $typeLabels, 0);
            $rawType    = self::COLUMN_TYPES[$typeChoice];

            // Handle enum: ask for values
            if ($rawType === 'enum') {
                $enumValues = $this->ask("  Enum values for {$field} (comma-separated, e.g. active,inactive,pending)", 'active,inactive');
                $quoted     = implode(',', array_map(
                    fn ($v) => "'" . trim($v) . "'",
                    explode(',', (string) $enumValues)
                ));
                $rawType = "enum({$quoted})";
            }

            // ── Nullable ───────────────────────────────────────────────────
            $nullable = $this->confirm("  Nullable?", false);

            // ── Default value ──────────────────────────────────────────────
            $defaultRaw   = $this->ask("  Default value (blank = none)", null);
            $defaultValue = ($defaultRaw !== null && trim($defaultRaw) !== '') ? trim($defaultRaw) : null;

            // Build a column descriptor matching what getColumns() returns
            $columns[] = (object) [
                'Field'   => $field,
                'Type'    => $rawType,
                'Null'    => $nullable ? 'YES' : 'NO',
                'Default' => $defaultValue,
                'Key'     => '',
                'Extra'   => '',
            ];

            $this->line("  <info>✓</info> {$field} ({$rawType})" . ($nullable ? ' nullable' : '') . ($defaultValue !== null ? " default={$defaultValue}" : ''));
            $this->newLine();
            $index++;
        }

        if (empty($columns)) {
            return [];
        }

        // ── Summary table ──────────────────────────────────────────────────
        $this->newLine();
        $this->line('<options=bold>Schema summary</>');
        $this->table(
            ['#', 'Column', 'Type', 'Nullable', 'Default'],
            array_map(fn ($i, $col) => [
                $i + 1,
                $col->Field,
                $col->Type,
                $col->Null === 'YES' ? 'YES' : 'NO',
                $col->Default ?? '',
            ], array_keys($columns), $columns)
        );
        $this->newLine();

        if (!$this->confirm('Proceed with this schema?', true)) {
            $this->line('Aborted. Re-run <info>crud:new</info> to start over.');
            return [];
        }

        return $columns;
    }

    // -----------------------------------------------------------------------
    // Route & nav injectors (identical logic to LivewireCrudGenerator)
    // -----------------------------------------------------------------------

    private function injectRoute(): void
    {
        $modulelower = Str::lower($this->getModuleInput());
        $module      = $this->getModuleInput();
        $this->filesystem = new Filesystem();

        $routeFile = base_path("Modules/{$module}/routes/web.php");
        if (! $this->filesystem->exists($routeFile)) {
            $this->warn("Route file not found: {$routeFile} — skipping route injection.");
            return;
        }

        $routeContents = $this->filesystem->get($routeFile);

        if ($this->option('sfc')) {
            // Volt single-file component route
            $namePluralLower = Str::lower(Str::plural($this->getNameInput()));
            $routeItemStub   = "\tVolt::route('/" . $this->getNameInput() . "', '{$modulelower}::{$namePluralLower}')->middleware('auth');";
        } else {
            $routeItemStub = "\tRoute::view('" . $this->getNameInput() . "', '{$modulelower}::livewire." . $this->getNameInput() . ".index')->middleware('auth');";
        }

        $routeItemHook = '//Route Hooks - Do not delete//';

        if (! Str::contains($routeContents, $routeItemStub)) {
            $newContents = str_replace($routeItemHook, $routeItemHook . PHP_EOL . $routeItemStub, $routeContents);
            $this->filesystem->put($routeFile, $newContents);
            $this->warn('Route inserted: <info>' . $routeFile . '</info>');
        }
    }

    private function injectNavLink(): void
    {
        $modulelower = Str::lower($this->getModuleInput());
        $menu        = Str::lower($this->getMenuInput());
        $this->filesystem ??= new Filesystem();

        $layoutFile = base_path("Modules/backend/resources/views/pmsmenu/{$menu}.blade.php");
        if (! $this->filesystem->exists($layoutFile)) {
            $this->warn("Nav file not found: {$layoutFile} — skipping nav injection.");
            return;
        }

        $layoutContents = $this->filesystem->get($layoutFile);
        $navItemStub    = "\t\t\t\t\t\t<li><a href=\"{{ url('/{$modulelower}/" . $this->getNameInput() . "') }}\"> " . ucfirst($this->getNameInput()) . "</a></li>";
        $navItemHook    = '<!--Nav Bar Hooks - Do not delete!!-->';

        if (! Str::contains($layoutContents, $navItemStub)) {
            $newContents = str_replace($navItemHook, $navItemHook . PHP_EOL . $navItemStub, $layoutContents);
            $this->filesystem->put($layoutFile, $newContents);
            $this->warn('Nav link inserted: <info>' . $layoutFile . '</info>');
        }
    }
}
