<?php

namespace Xslainadmin\LivewireCrud\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;
use File;

class LivewireCrudGenerator extends LivewireGeneratorCommand
{

	protected $filesystem;
    protected $stubDir;
    protected $argument;
    private $replaces = [];

    protected $signature = 'crud:generate {name : Table name} {theme} {menu} {module}';

    protected $description = 'Generate Livewire Component and CRUD operations';

    /**
     * Execute the console command.
     * @return bool|null
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        $this->table = $this->getNameInput();
        $this->module = $this->getModuleInput();
        $this->theme = $this->getThemeInput();
        $this->menu = $this->getMenuInput();

        // If table not exist in DB return
        if (!$this->tableExists()) {
            $this->error("`{$this->table}` table not exist");

            return false;
        }

        // Build the class name from table name
        $this->name = $this->_buildClassName();

        // Generate the crud
           $this->buildModel()
				->buildViews();

		//Updating Routes
        $modulelower = Str::lower($this->getModuleInput());
        $module = $this->getModuleInput();
        $modulename = $this->getNameInput();
        $menu = Str::lower($this->getMenuInput());
        $theme = $this->getThemeInput();
        $this->filesystem = new Filesystem;
        $this->argument = $this->getNameInput();
        $routeFile = base_path("Modules/{$module}/routes/web.php");
        $routeContents = $this->filesystem->get($routeFile);
        if ($this->getThemeInput() == 'none') {
            $routeItemStub = "\tRoute::view('" .     $this->getNameInput() . "', '{$modulelower}::livewire." . $this->getNameInput() . ".index')->middleware('auth');";
            $routeUploadStub = "\tRoute::post('" .     $this->getNameInput() . "/upload-photo', [Modules\\{$module}\\App\\Http\\Controllers\\" . Str::studly(Str::plural($this->getNameInput())) . "Controller::class, 'uploadPhoto'])->name('{$modulename}.upload-photo')->middleware('auth');";
        }elseif ($this->getThemeInput() == 'nonedefault') {
            $routeItemStub = "\tRoute::view('" .     $this->getNameInput() . "', '{$modulelower}::livewire." . $this->getNameInput() . ".index')->middleware('auth');";
            $routeUploadStub = "\tRoute::post('" .     $this->getNameInput() . "/upload-photo', [Modules\\{$module}\\App\\Http\\Controllers\\" . Str::studly(Str::plural($this->getNameInput())) . "Controller::class, 'uploadPhoto'])->name('{$modulename}.upload-photo')->middleware('auth');";
        }else {
            $routeItemStub = "\tRoute::view('" .     $this->getNameInput() . "', '{$modulelower}::livewire." . $this->getNameInput() . ".index')->middleware('auth');";
            $routeUploadStub = "\tRoute::post('" .     $this->getNameInput() . "/upload-photo', [Modules\\{$module}\\App\\Http\\Controllers\\" . Str::studly(Str::plural($this->getNameInput())) . "Controller::class, 'uploadPhoto'])->name('{$modulename}.upload-photo')->middleware('auth');";
        }
		$routeItemHook = '//Route Hooks - Do not delete//';

        if (!Str::contains($routeContents, $routeItemStub)) {
            $newContents = str_replace($routeItemHook, $routeItemHook . PHP_EOL . $routeItemStub, $routeContents);
            $this->filesystem->put($routeFile, $newContents);
            $this->warn('Route inserted: <info>' . $routeFile . '</info>');
            $routeContents = $newContents; // Update routeContents for next check
        }

        // Add upload-photo route if it doesn't exist
        if (!Str::contains($routeContents, $routeUploadStub)) {
            $newContents = str_replace($routeItemHook, $routeItemHook . PHP_EOL . $routeUploadStub, $routeContents);
            $this->filesystem->put($routeFile, $newContents);
            $this->warn('Upload photo route inserted: <info>' . $routeFile . '</info>');
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

        return true;
    }

    /**
     * @return $this
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildModel()
    {
        $modulelower = Str::lower($this->getModuleInput());
        $module = $this->getModuleInput();
        $theme = $this->getThemeInput();
        $modelPath = $this->_getCreateModelPath($this->name);
        $importPath = $this->_getImportPath($this->name);
        $exportPath = $this->_getExportPath($this->name);
        $notificationPath = $this->_getNotificationPath($this->name);
        $emailPath = $this->_getEmailPath($this->name);
        $chartPath = $this->_getChartPath($this->name);
        $uploadPath = $this->_getUploadPath($this->name);
        $fullcalendarPath = $this->_getFullcalendarPath($this->name);
        // $factoryPath = $this->_getFactoryPath($this->name);
        $printPath = $this->_getPrintPath($this->name);
        $pdfexportPath = $this->_getPdfExportPath($this->name);
        // $modelPath = $this->_getModelPath($this->name);
        // $createlPath = $this->_getCreatePath($this->name);
        // $deletePath = $this->_getDeletePath($this->name);
        // $editPath = $this->_getEditPath($this->name);
        // $showPath = $this->_getShowPath($this->name);
		$livewirePath = $this->_getLivewirePath($this->name);
        $modulePath = trim($this->_getModulePath($this->name), '{}');
        $factoryPath = $this->_getFactoryPath($this->name);

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

        if ($theme == 'none') {
        $livewireTemplate = str_replace(
            array_keys($replace), array_values($replace), $this->getStub('Livewire')
        );
        // $editTemplate = str_replace(
        //     array_keys($replace), array_values($replace), $this->getStub("modals/Edit")
        // );
        // $createTemplate = str_replace(
        //     array_keys($replace), array_values($replace), $this->getStub("modals/Create")
        // );
        // $showTemplate = str_replace(
        //     array_keys($replace), array_values($replace), $this->getStub("modals/Show")
        // );
        //  $deleteTemplate = str_replace(
        //     array_keys($replace), array_values($replace), $this->getStub("modals/Delete")
        // );
        }else{
            $livewireTemplate = str_replace(
                array_keys($replace),
                array_values($replace),
                $this->getStub('Livewirethemed')
            );
            // $editTemplate = str_replace(
            //     array_keys($replace),
            //     array_values($replace),
            //     $this->getStub("modalsthemed/Edit")
            // );
            // $createTemplate = str_replace(
            //     array_keys($replace),
            //     array_values($replace),
            //     $this->getStub("modalsthemed/Create")
            // );
            // $showTemplate = str_replace(
            //     array_keys($replace),
            //     array_values($replace),
            //     $this->getStub("modalsthemed/Show")
            // );
            // $deleteTemplate = str_replace(
            //     array_keys($replace),
            //     array_values($replace),
            //     $this->getStub("modalsthemed/Delete")
            // );
        }
        $this->warn('Creating: <info>Livewire Component...</info>');
        // $this->write($livewirePath, $livewireTemplate);
        $this->write($modulePath, $livewireTemplate);
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


        return $this;
    }

    /**
     * @return $this
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Exception
     */
    protected function buildViews()
    {
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
            $columnDatatype = $this->determineFieldType($datatype);
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
            '{{tableHeader}}' => $tableHead,
            '{{tableBody}}' => $tableBody,
            '{{viewRows}}' => $viewRows,
            // '{{form}}' => $form,
            '{{form}}' => $formdata,
            '{{show}}' => $show,
            '{{type}}' => $type,
            '{{showfields}}' => $showfields,
            '{{themelower}}' => Str::lower($this->getThemeInput()),
            '{{getModuleInputClass}}' => Str::studly($this->getModuleInput()),
            '{{getModuleInput}}' => $this->getModuleInput(),
            '{{getModuleInputLower}}' => Str::lower($this->getModuleInput()),
            '{{getThemeInput}}' => $this->getThemeInput(),
            '{{getThemeInputLower}}' => Str::lower($this->getThemeInput()),
            '{{getNameInput}}' => $this->getNameInput(),
            '{{getNameInputLower}}' => Str::lower($this->getNameInput()),
            '{{getNameInputPluralLower}}' => Str::lower(Str::plural($this->getNameInput())),
        ]);

        $this->buildLayout();
        foreach (['view', 'index', 'create', 'delete', 'show', 'import', 'update', 'pdf-export', 'print','mobile_index','mobile_index'] as $view) {
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
    private function _buildClassName()
    {
        return Str::studly(Str::singular($this->table));
    }

    /**
     * Determine the input field type based on database column type
     *
     * @param string $dbColumnType The database column type (e.g., 'varchar(255)', 'int', 'timestamp')
     * @return string The HTML input type or field type
     */
    private function determineFieldType($dbColumnType)
    {
        $columnType = strtolower($dbColumnType);

        // Determine input type based on column type
        if (Str::contains($columnType, ['timestamp', 'date', 'datetime'])) {
            return 'date';
        } elseif (Str::contains($columnType, ['int', 'integer', 'bigint', 'smallint', 'tinyint'])) {
            return 'number';
        } elseif (Str::contains($columnType, 'time')) {
            return 'time';
        } elseif (Str::contains($columnType, ['text', 'longtext', 'mediumtext'])) {
            return 'textarea';
        } elseif (Str::contains($columnType, ['decimal', 'float', 'double', 'numeric'])) {
            return 'number';
        } elseif (Str::contains($columnType, ['bool', 'boolean', 'tinyint(1)'])) {
            return 'checkbox';
        } elseif (Str::contains($columnType, 'enum')) {
            return 'select';
        } elseif (Str::contains($columnType, 'json')) {
            return 'textarea';
        } elseif (Str::contains($columnType, ['char', 'varchar', 'string'])) {
            return 'text';
        }

        return 'text'; // Default
    }

    /**
     * Get the column type for a specific column name
     *
     * @param string $columnName
     * @return string
     */
    private function getColumnType($columnName)
    {
        $columns = $this->getColumns();

        foreach ($columns as $column) {
            if ($column->Field === $columnName) {
                return $this->determineFieldType($column->Type);
            }
        }

        return 'text'; // Default fallback
    }

	private function replace($content)
    {
        foreach ($this->replaces as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }

        return $content;
    }
}
