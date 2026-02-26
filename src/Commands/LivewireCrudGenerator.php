<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Commands;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Xslainadmin\LivewireCrud\Support\StubTokens;

class LivewireCrudGenerator extends LivewireGeneratorCommand
{

    protected Filesystem $filesystem;
    protected string $stubDir;
    protected string $argument;

    /** @var array<string, string> */
    private array $replaces = [];

    protected $signature = 'crud:generate {name : Table name} {theme} {menu} {module}
        {--sfc : Generate a Livewire Volt single-file component (requires livewire/volt)}';

    protected $description = 'Generate Livewire Component and CRUD operations';

    /**
     * Execute the console command.
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle(): int
    {
        $this->table = $this->getNameInput();
        $this->module = $this->getModuleInput();
        $this->theme = $this->getThemeInput();
        $this->menu = $this->getMenuInput();

        // If table not exist in DB return
        if (!$this->tableExists()) {
            $this->error("`{$this->table}` table not exist");

            return self::FAILURE;
        }

        // Build the class name from table name
        $this->modelName = $this->_buildClassName();

        // Generate the crud
           $this->buildModel()
				->buildViews();

		//Updating Routes
        $modulelower = Str::lower($this->getModuleInput());
        $module = $this->getModuleInput();
        $menu = Str::lower($this->getMenuInput());
        $theme = $this->getThemeInput();
        $this->filesystem = new Filesystem;
        $this->argument = $this->getNameInput();
        $routeFile = base_path("Modules/{$module}/routes/web.php");
        $routeContents = $this->filesystem->get($routeFile);
        if ($this->option('sfc')) {
            // Volt::route() — single-file component (requires livewire/volt)
            $namePluralLower = Str::lower(Str::plural($this->getNameInput()));
            $routeItemStub = "\tVolt::route('/" . $this->getNameInput() . "', '{$modulelower}::{$namePluralLower}')->middleware('auth');";
        } elseif ($this->getThemeInput() == 'none') {
            $routeItemStub = "\tRoute::view('" .     $this->getNameInput() . "', '{$modulelower}::livewire." . $this->getNameInput() . ".index')->middleware('auth');";
        } elseif ($this->getThemeInput() == 'nonedefault') {
            $routeItemStub = "\tRoute::view('" .     $this->getNameInput() . "', '{$modulelower}::livewire." . $this->getNameInput() . ".index')->middleware('auth');";
        } else {
            $routeItemStub = "\tRoute::view('" .     $this->getNameInput() . "', '{$modulelower}::livewire." . $this->getNameInput() . ".index')->middleware('auth');";
        }
		$routeItemHook = '//Route Hooks - Do not delete//';

        if (!Str::contains($routeContents, $routeItemStub)) {
            $newContents = str_replace($routeItemHook, $routeItemHook . PHP_EOL . $routeItemStub, $routeContents);
            $this->filesystem->put($routeFile, $newContents);
            $this->warn('Route inserted: <info>' . $routeFile . '</info>');
        }

		//Updating Nav Bar
        // $layoutFile = 'resources/views/layouts/app.blade.php';
		$layoutFile = base_path("Modules/backend/resources/views/pmsmenu/{$menu}.blade.php");
        $layoutContents = $this->filesystem->get($layoutFile);
		$navItemStub = "\t\t\t\t\t\t<li><a href=\"{{ url('/{$modulelower}/" . $this->getNameInput() . "') }}\"> " . ucfirst($this->getNameInput()) . "</a></li>";
        // $navItemStub = "\t\t\t\t\t\t<li class=\"nav-item\"><a href=\"{{ url('/".$this->getNameInput()."') }}\" class=\"nav-link\"><i class=\"fab fa-laravel text-info\"></i> ". ucfirst($this->getNameInput()) ."</a></li>";
        $navItemHook = '<!--Nav Bar Hooks - Do not delete!!-->';

        if (!Str::contains($layoutContents, $navItemStub)) {
            $newContents = str_replace($navItemHook, $navItemHook . PHP_EOL . $navItemStub, $layoutContents);
            $this->filesystem->put($layoutFile, $newContents);
            $this->warn('Nav link inserted: <info>' . $layoutFile . '</info>');
        }

        $this->info('');
        $this->info('Livewire Component & CRUD Generated Successfully.');

        return self::SUCCESS;
    }

    /**
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function buildModel(): static
    {
        $modulelower = Str::lower($this->getModuleInput());
        $module = $this->getModuleInput();
        $theme = $this->getThemeInput();
        $modelPath = $this->_getCreateModelPath($this->modelName);
        $importPath = $this->_getImportPath($this->modelName);
        $exportPath = $this->_getExportPath($this->modelName);
        $notificationPath = $this->_getNotificationPath($this->modelName);
        $emailPath = $this->_getEmailPath($this->modelName);
        $chartPath = $this->_getChartPath($this->modelName);
        $uploadPath = $this->_getUploadPath($this->modelName);
        $fullcalendarPath = $this->_getFullcalendarPath($this->modelName);
        // $factoryPath = $this->_getFactoryPath($this->modelName);
        $printPath = $this->_getPrintPath($this->modelName);
        $pdfexportPath = $this->_getPdfExportPath($this->modelName);
        // $modelPath = $this->_getModelPath($this->modelName);
        // $createlPath = $this->_getCreatePath($this->modelName);
        // $deletePath = $this->_getDeletePath($this->modelName);
        // $editPath = $this->_getEditPath($this->modelName);
        // $showPath = $this->_getShowPath($this->modelName);
		$livewirePath = $this->_getLivewirePath($this->modelName);
        $modulePath = trim($this->_getModulePath($this->modelName), '{}');
        $factoryPath = $this->_getFactoryPath($this->modelName);

        // ── Enterprise paths ──────────────────────────────────────────────
        $policyPath        = $this->_getPolicyPath($this->modelName);
        $observerPath      = $this->_getObserverPath($this->modelName);
        $servicePath       = $this->_getServicePath($this->modelName);
        $apiControllerPath = $this->_getApiControllerPath($this->modelName);
        $apiResourcePath   = $this->_getApiResourcePath($this->modelName);
        $seederPath        = $this->_getSeederPath($this->modelName);
        $featureTestPath   = $this->_getFeatureTestPath($this->modelName);

        if ($this->files->exists($modulePath) && $this->ask("Livewire Component ". Str::studly(Str::singular($this->table)) ."Component Already exist. Do you want overwrite (y/n)?", 'y') == 'n') {
            return $this;
        }

        // Make Replacements in Model / Livewire / Migrations / Factories
        $replace = array_merge($this->buildReplacements(), $this->modelReplacements());

        $modelTemplate = str_replace(
            array_keys($replace), array_values($replace), $this->getStub('Model')
        );
		$factoryTemplate = str_replace(
            array_keys($replace), array_values($replace), $this->getStub('Factory')
        );
        $importTemplate = str_replace(
            array_keys($replace), array_values($replace), $this->getStub('Import')
        );
        $exportTemplate = str_replace(
            array_keys($replace), array_values($replace), $this->getStub('Export')
        );
        $notificationTemplate = str_replace(
            array_keys($replace), array_values($replace), $this->getStub('Notification')
        );
        $emailTemplate = str_replace(
            array_keys($replace), array_values($replace), $this->getStub('Email')
        );
        $chartTemplate = str_replace(
            array_keys($replace), array_values($replace), $this->getStub('Chart')
        );
        $fullcalendarTemplate = str_replace(
            array_keys($replace), array_values($replace), $this->getStub('Fullcalendar')
        );
        $printTemplate = str_replace(
            array_keys($replace), array_values($replace), $this->getStub('Print')
        );
        $pdfexportTemplate = str_replace(
            array_keys($replace), array_values($replace), $this->getStub('PdfExport')
        );
        $uploadTemplate = str_replace(
            array_keys($replace), array_values($replace), $this->getStub('Upload')
        );

        // ── Enterprise templates ──────────────────────────────────────────
        $policyTemplate = str_replace(
            array_keys($replace), array_values($replace), $this->getStub('Policy')
        );
        $observerTemplate = str_replace(
            array_keys($replace), array_values($replace), $this->getStub('Observer')
        );
        $serviceTemplate = str_replace(
            array_keys($replace), array_values($replace), $this->getStub('Service')
        );
        $apiControllerTemplate = str_replace(
            array_keys($replace), array_values($replace), $this->getStub('ApiController')
        );
        $apiResourceTemplate = str_replace(
            array_keys($replace), array_values($replace), $this->getStub('ApiResource')
        );
        $seederTemplate = str_replace(
            array_keys($replace), array_values($replace), $this->getStub('Seeder')
        );
        $featureTestTemplate = str_replace(
            array_keys($replace), array_values($replace), $this->getStub('FeatureTest')
        );

        if ($this->option('sfc')) {
            // ── Volt single-file component mode ───────────────────────────
            $voltTemplate = str_replace(
                array_keys($replace), array_values($replace), $this->getStub('LivewireVolt')
            );
            $this->warn('Creating: <info>Livewire Volt Single-File Component...</info>');
            $this->write($this->_getVoltComponentPath(), $voltTemplate);
            $this->info('  <fg=yellow>Note:</> Add <comment>Volt::mount()</comment> in your module service provider to register the component.');
            $this->info('  <fg=yellow>Route:</> <comment>Volt::route(\'/{{nameInput}}\', \'{{moduleLower}}::{{namePluralLower}}\')->middleware(\'auth\');</comment>');
        } else {
            // ── Classic split component / views mode ──────────────────────
            if ($theme == 'none') {
            $livewireTemplate = str_replace(
                array_keys($replace), array_values($replace), $this->getStub('Livewire')
            );
            }else{
                $livewireTemplate = str_replace(
                    array_keys($replace),
                    array_values($replace),
                    $this->getStub('Livewirethemed')
                );
            }
            $this->warn('Creating: <info>Livewire Component...</info>');
            $this->write($modulePath, $livewireTemplate);
        }
		$this->warn('Creating: <info>Model...</info>');
        //start Create
        // $this->write($createlPath, $createTemplate);
		// $this->warn('Creating: <info>Create...</info>');
        //end create
        //start edit
        // $this->write($editPath, $editTemplate);
		// $this->warn('Creating: <info>Edit...</info>');
        //end edit
        //start Create
        // $this->write($showPath, $showTemplate);
		// $this->warn('Creating: <info>Show...</info>');
        //end create
        //start Create
        // $this->write($deletePath, $deleteTemplate);
		// $this->warn('Creating: <info>Delete...</info>');
        //end create
        $this->write($modelPath, $modelTemplate);
        $this->warn('Creating: <info>Factories, Please edit before running Factory ...</info>');
        $this->write($factoryPath, $factoryTemplate);
        $this->warn('Creating: <info>Import, Please edit before using ...</info>');
        $this->write($importPath, $importTemplate);
        $this->warn('Creating: <info>Export, Please edit before using ...</info>');
        $this->write($exportPath, $exportTemplate);
        $this->warn('Creating: <info>Notification, Please edit before using ...</info>');
        $this->write($notificationPath, $notificationTemplate);
        $this->warn('Creating: <info>Email, Please edit before using ...</info>');
        $this->write($emailPath, $emailTemplate);
        $this->warn('Creating: <info>Chart, Please edit before using ...</info>');
        $this->write($chartPath, $chartTemplate);
        $this->warn('Creating: <info>Fullcalendar, Please edit before using ...</info>');
        $this->write($fullcalendarPath, $fullcalendarTemplate);
        $this->warn('Creating: <info>Print, Please edit before using ...</info>');
        $this->write($printPath, $printTemplate);
        $this->warn('Creating: <info>Pdf Export, Please edit before using ...</info>');
        $this->write($pdfexportPath, $pdfexportTemplate);
        $this->warn('Creating: <info>Upload, Please edit before using ...</info>');
        $this->write($uploadPath, $uploadTemplate);

        // ── Enterprise files ──────────────────────────────────────────────
        $this->warn('Creating: <info>Policy (Authorization)...</info>');
        $this->write($policyPath, $policyTemplate);

        $this->warn('Creating: <info>Observer (Model lifecycle hooks)...</info>');
        $this->write($observerPath, $observerTemplate);

        $this->warn('Creating: <info>Service (Business logic layer)...</info>');
        $this->write($servicePath, $serviceTemplate);

        $this->warn('Creating: <info>API Controller (REST endpoints)...</info>');
        $this->write($apiControllerPath, $apiControllerTemplate);

        $this->warn('Creating: <info>API Resource (JSON transformer)...</info>');
        $this->write($apiResourcePath, $apiResourceTemplate);

        $this->warn('Creating: <info>Database Seeder...</info>');
        $this->write($seederPath, $seederTemplate);

        $this->warn('Creating: <info>Feature Test...</info>');
        $this->write($featureTestPath, $featureTestTemplate);

        // ── Migration ─────────────────────────────────────────────────────
        $this->buildMigration();

        // ── Resource ──────────────────────────────────────────────────────
        $resourcePath = $this->_getResourcePath();
        $resourceTemplate = str_replace(
            array_keys($replace), array_values($replace), $this->getStub('Resource')
        );
        $this->warn('Creating: <info>Resource class...</info>');
        $this->write($resourcePath, $resourceTemplate);

        return $this;
    }

    /**
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Exception
     */
    public function buildViews(): static
    {
        // In single-file component mode the view is embedded in the Volt stub.
        if ($this->option('sfc')) {
            $this->info('Skipping separate view files (--sfc mode: view is embedded in the Volt component).');
            return $this;
        }

        $theme = $this->getThemeInput();
        $this->warn('Creating:<info> Views ...</info>');

        $tableHead = "\n";
        $tableBody = "\n";
        $viewRows = "\n";
        $form = "\n";
        $show = "\n";
        $showfields = "\n";
        $type = 'text';
        $formdata = "\n";

        // Build comprehensive column types mapping FIRST for ALL columns
        $columnTypes = [];
        $columns = $this->getColumns();

        // Debug: Show column count
        $this->info("Processing " . count($columns) . " columns from table '{$this->table}'");

        foreach ($columns as $index => $column) {
            // Ensure column object has required properties
            if (!isset($column->Field) || !isset($column->Type)) {
                $this->warn("Column at index {$index} missing Field or Type property. Skipping...");
                continue;
            }

            $columnTypes[$column->Field] = $this->determineFieldType($column->Type);
        }

        // Debug: Show processed columns
        $this->info("Processed column types: " . json_encode($columnTypes));
        // Build form fields and table structure with proper column types
        foreach ($this->getFilteredColumns() as $columnName) {
            $title = Str::title(str_replace('_', ' ', $columnName));

            // Find the full column object to get the datatype
            $columnObject = null;
            foreach ($columns as $col) {
                if ($col->Field === $columnName) {
                    $columnObject = $col;
                    break;
                }
            }

            // Get datatype from column object
            $datatype = $columnObject ? $columnObject->Type : 'text';
            $columnDatatype = $this->typeMapper()->htmlInputType($columnName, $datatype);
            $tableHead .= "\t\t\t\t" . $this->getHead($title);
            $tableBody .= "\t\t\t\t" . $this->getBody($columnName);

            // Get the column type from our pre-built mapping
            $columnType = $columnTypes[$columnName] ?? 'text';

            $form .= $this->getField($title, $columnName, 'form-field');
            $form .= "\n";
            $formdata .= $this->getDataField($title, $columnName, $columnDatatype);
            $formdata .= "\n";
            $show .= $this->showField($title, $columnName, 'show-field');
            $show .= "\n";
            $viewRows .= $this->getField($title, $columnName, 'view-row', $columnType);
            $viewRows .= "\n";

            $this->info("Column: {$columnName}, Type: {$columnType}, Datatype: {$datatype}");
            $showfields .= $this->showField($title, $columnName, 'show-field');
            $showfields .= "\n";
        }

        // Use the last column's type as default for backward compatibility
        // $type = $columnType;

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
            if ($this->getThemeInput() == 'none') {
                $viewTemplate = str_replace(
                    array_keys($replace),
                    array_values($replace),
                    $this->getStub("views/{$view}")
                );
            }elseif ($this->getThemeInput() == 'nonedefault') {
                $viewTemplate = str_replace(
                    array_keys($replace),
                    array_values($replace),
                    $this->getStub("viewsdefault/{$view}")
                );
            } else {
                $viewTemplate = str_replace(
                    array_keys($replace),
                    array_values($replace),
                    $this->getStub("themes/{$theme}/views/{$view}")
                );
            }
            $this->write($this->_getViewPath($view), $viewTemplate);
        }

        return $this;
    }

    /**
     * Make the class name from table name.
     *
     * @return string
     */
    private function _buildClassName(): string
    {
        return Str::studly(Str::singular($this->table));
    }

    /**
     * Determine the input field type based on database column type.
     * Delegates to the shared TypeMapper for consistent mapping.
     *
     * @param string $dbColumnType The database column type (e.g., 'varchar(255)', 'int', 'timestamp')
     * @return string The HTML input type or field type
     */
    private function determineFieldType(string $dbColumnType): string
    {
        // Use a temporary field name; TypeMapper::htmlInputType also accepts field name hints
        return $this->typeMapper()->htmlInputType('', $dbColumnType);
    }

    /**
     * Get the column type for a specific column name.
     */
    private function getColumnType(string $columnName): string
    {
        foreach ($this->getColumns() as $column) {
            if ($column->Field === $columnName) {
                return $this->typeMapper()->htmlInputType($columnName, $column->Type);
            }
        }

        return 'text';
    }

    private function replace(string $content): string
    {
        foreach ($this->replaces as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }

        return $content;
    }
}
