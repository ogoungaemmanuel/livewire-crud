<?php

namespace Xslainadmin\LivewireCrud\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

class TraditionalCrudGenerator extends LivewireGeneratorCommand
{
    protected $filesystem;
    protected $stubDir;
    protected $argument;
    private $replaces = [];

    protected $signature = 'crud:traditional {name : Table name} {theme} {menu} {module}';

    protected $description = 'Generate Traditional Laravel MVC CRUD (Controller, Views, Routes)';

    /**
     * Execute the console command.
     *
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

        // Generate the crud components
        $this->buildModel()
            ->buildController()
            ->buildFormRequest()
            ->buildViews()
            ->buildRoutes();

        $this->info('');
        $this->info('Traditional Laravel CRUD Generated Successfully.');
        $this->info('');
        $this->info('Next steps:');
        $this->info('1. Review generated controller: Modules/' . $this->module . '/Http/Controllers/' . $this->name . 'Controller.php');
        $this->info('2. Customize Form Request validation: Modules/' . $this->module . '/Http/Requests/' . $this->name . 'Request.php');
        $this->info('3. Test routes: php artisan route:list --name=' . Str::lower($this->table));
        $this->info('4. Access in browser: /' . Str::lower($this->module) . '/' . Str::lower($this->table));

        return true;
    }

    /**
     * Initialize filesystem.
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct($files);
        $this->filesystem = $files;
    }

    /**
     * Build the Controller.
     *
     * @return $this
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildController()
    {
        $controllerPath = $this->_getControllerPath($this->name);
        
        if ($this->filesystem->exists($controllerPath) && !$this->confirm("Controller already exists. Overwrite?")) {
            return $this;
        }

        $this->info('Creating Controller...');

        $stub = $this->filesystem->get(__DIR__ . '/../stubs/Controller.stub');
        
        // Generate search fields for the controller
        $searchFields = $this->generateSearchFields();
        
        // Replace placeholders using parent method
        $stub = $this->buildStub($stub, [
            'searchFields' => $searchFields,
        ]);

        $this->makeDirectory($controllerPath);
        $this->filesystem->put($controllerPath, $stub);
        $this->info('Controller created: ' . $controllerPath);

        return $this;
    }

    /**
     * Build the Form Request class.
     *
     * @return $this
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildFormRequest()
    {
        $requestPath = $this->_getFormRequestPath($this->name);
        
        if ($this->filesystem->exists($requestPath) && !$this->confirm("Form Request already exists. Overwrite?")) {
            return $this;
        }

        $this->info('Creating Form Request...');

        $stub = $this->filesystem->get(__DIR__ . '/../stubs/FormRequest.stub');
        
        // Generate validation rules
        $validationRules = $this->generateValidationRules();
        $attributeNames = $this->generateAttributeNames();
        $customMessages = $this->generateCustomMessages();
        
        // Replace placeholders using parent method
        $stub = $this->buildStub($stub, [
            'validationRules' => $validationRules,
            'attributeNames' => $attributeNames,
            'customMessages' => $customMessages,
        ]);

        $this->makeDirectory($requestPath);
        $this->filesystem->put($requestPath, $stub);
        $this->info('Form Request created: ' . $requestPath);

        return $this;
    }

    /**
     * Build traditional views.
     *
     * @return $this
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildViews()
    {
        $this->info('Creating Views...');

        $views = ['index', 'create', 'edit', 'show', 'pdf'];
        
        foreach ($views as $view) {
            $this->buildView($view);
        }

        return $this;
    }

    /**
     * Build individual view.
     *
     * @param string $viewName
     * @return void
     */
    protected function buildView($viewName)
    {
        $viewPath = $this->_getViewPath($viewName);
        
        $stub = $this->filesystem->get(__DIR__ . "/../stubs/traditionalmvc/{$viewName}.stub");
        
        // Generate view-specific content
        $replacements = [];
        
        switch ($viewName) {
            case 'index':
                $replacements['tableHeaders'] = $this->generateTableHeaders();
                $replacements['tableColumns'] = $this->generateTableColumns();
                break;
            case 'create':
                $replacements['formFields'] = $this->generateFormFields();
                break;
            case 'edit':
                $replacements['editFormFields'] = $this->generateEditFormFields();
                break;
            case 'show':
                $replacements['showFields'] = $this->generateShowFields();
                $replacements['relatedSections'] = $this->generateRelatedSections();
                break;
            case 'pdf':
                $replacements['pdfTableHeaders'] = $this->generatePdfTableHeaders();
                $replacements['pdfTableColumns'] = $this->generatePdfTableColumns();
                break;
        }
        
        $stub = $this->buildStub($stub, $replacements);
        $this->makeDirectory($viewPath);
        $this->filesystem->put($viewPath, $stub);
        
        $this->info("View created: {$viewPath}");
    }

    /**
     * Build routes.
     *
     * @return $this
     */
    protected function buildRoutes()
    {
        $this->info('Updating Routes...');

        $moduleLower = Str::lower($this->getModuleInput());
        $module = $this->getModuleInput();
        $routeFile = base_path("Modules/{$module}/routes/web.php");
        
        if (!$this->filesystem->exists($routeFile)) {
            $this->error("Route file not found: {$routeFile}");
            return $this;
        }

        $routeContents = $this->filesystem->get($routeFile);
        
        // Generate RESTful resource routes
        $controllerName = $this->name . 'Controller';
        $routeName = Str::lower($this->getNameInput());
        
        $routeStub = $this->generateResourceRoutes($moduleLower, $routeName, $controllerName);
        $routeHook = '//Route Hooks - Do not delete//';

        if (!Str::contains($routeContents, "Route::resource('{$routeName}'")) {
            $newContents = str_replace($routeHook, $routeHook . PHP_EOL . $routeStub, $routeContents);
            $this->filesystem->put($routeFile, $newContents);
            $this->warn('Routes inserted: ' . $routeFile);
        } else {
            $this->warn('Routes already exist, skipping...');
        }

        // Update navigation
        $this->updateNavigation($moduleLower, $routeName);

        return $this;
    }

    /**
     * Generate RESTful resource routes.
     *
     * @param string $module
     * @param string $routeName
     * @param string $controller
     * @return string
     */
    protected function generateResourceRoutes($module, $routeName, $controller)
    {
        return <<<EOT

    // {$this->name} CRUD Routes
    Route::resource('{$routeName}', \\Modules\\{$this->module}\\Http\\Controllers\\{$controller}::class)
        ->names([
            'index' => '{$module}.{$routeName}.index',
            'create' => '{$module}.{$routeName}.create',
            'store' => '{$module}.{$routeName}.store',
            'show' => '{$module}.{$routeName}.show',
            'edit' => '{$module}.{$routeName}.edit',
            'update' => '{$module}.{$routeName}.update',
            'destroy' => '{$module}.{$routeName}.destroy',
        ])
        ->middleware('auth');
    
    // Additional routes for {$this->name}
    Route::delete('{$routeName}/bulk-delete', [\\Modules\\{$this->module}\\Http\\Controllers\\{$controller}::class, 'bulkDelete'])
        ->name('{$module}.{$routeName}.bulk-delete')
        ->middleware('auth');
    Route::get('{$routeName}/export/excel', [\\Modules\\{$this->module}\\Http\\Controllers\\{$controller}::class, 'exportExcel'])
        ->name('{$module}.{$routeName}.export.excel')
        ->middleware('auth');
    Route::get('{$routeName}/export/pdf', [\\Modules\\{$this->module}\\Http\\Controllers\\{$controller}::class, 'exportPdf'])
        ->name('{$module}.{$routeName}.export.pdf')
        ->middleware('auth');
    Route::post('{$routeName}/import', [\\Modules\\{$this->module}\\Http\\Controllers\\{$controller}::class, 'import'])
        ->name('{$module}.{$routeName}.import')
        ->middleware('auth');
EOT;
    }

    /**
     * Generate search fields for controller.
     *
     * @return string
     */
    protected function generateSearchFields()
    {
        $fields = [];
        foreach ($this->getFilteredColumns() as $column) {
            $columnName = $column['name'];
            if (in_array($column['type'], ['string', 'text'])) {
                $fields[] = "\$q->where('{$columnName}', 'LIKE', \"%{\$searchTerm}%\")";
            }
        }
        
        return implode("\n                    ->orWhere(", $fields);
    }

    /**
     * Get controller path.
     *
     * @param string $name
     * @return string
     */
    protected function _getControllerPath($name)
    {
        return base_path("Modules/{$this->module}/Http/Controllers/{$name}Controller.php");
    }

    /**
     * Get form request path.
     *
     * @param string $name
     * @return string
     */
    protected function _getFormRequestPath($name)
    {
        return base_path("Modules/{$this->module}/Http/Requests/{$name}Request.php");
    }

    /**
     * Get view path.
     *
     * @param string $viewName
     * @return string
     */
    protected function _getViewPath($viewName)
    {
        $tableLower = Str::lower($this->table);
        $viewDir = base_path("Modules/{$this->module}/resources/views/{$tableLower}");
        
        if (!$this->filesystem->exists($viewDir)) {
            $this->filesystem->makeDirectory($viewDir, 0755, true);
        }
        
        return "{$viewDir}/{$viewName}.blade.php";
    }

    /**
     * Update navigation menu.
     *
     * @param string $module
     * @param string $routeName
     * @return void
     */
    protected function updateNavigation($module, $routeName)
    {
        $menu = $this->menu;
        $layoutFile = base_path("Modules/backend/resources/views/pmsmenu/{$menu}.blade.php");
        
        if (!$this->filesystem->exists($layoutFile)) {
            $this->warn("Navigation file not found: {$layoutFile}");
            return;
        }

        $layoutContents = $this->filesystem->get($layoutFile);
        $navItemStub = "\t\t\t\t\t\t<li><a href=\"{{ url('/{$module}/" . $this->getNameInput() . "') }}\"> " . ucfirst($this->getNameInput()) . "</a></li>";
        $navItemHook = '<!--Nav Bar Hooks - Do not delete!!-->';

        if (!Str::contains($layoutContents, $navItemStub)) {
            $newContents = str_replace($navItemHook, $navItemHook . PHP_EOL . $navItemStub, $layoutContents);
            $this->filesystem->put($layoutFile, $newContents);
            $this->warn('Nav link inserted: ' . $layoutFile);
        }
    }

    /**
     * Generate validation rules from database columns.
     *
     * @return string
     */
    protected function generateValidationRules()
    {
        $rules = [];
        
        foreach ($this->getColumns() as $column) {
            if (in_array($column->Field, $this->unwantedColumns)) {
                continue;
            }

            $ruleArray = [];
            
            // Required check
            if ($column->Null === 'NO' && $column->Default === null) {
                $ruleArray[] = 'required';
            } else {
                $ruleArray[] = 'nullable';
            }

            // Type-specific rules
            $type = $this->getColumnType($column->Type);
            
            switch ($type) {
                case 'string':
                    $ruleArray[] = 'string';
                    if (preg_match('/varchar\((\d+)\)/', $column->Type, $matches)) {
                        $ruleArray[] = "max:{$matches[1]}";
                    }
                    break;
                case 'text':
                    $ruleArray[] = 'string';
                    break;
                case 'integer':
                    $ruleArray[] = 'integer';
                    break;
                case 'decimal':
                case 'float':
                    $ruleArray[] = 'numeric';
                    break;
                case 'boolean':
                    $ruleArray[] = 'boolean';
                    break;
                case 'date':
                    $ruleArray[] = 'date';
                    break;
                case 'datetime':
                    $ruleArray[] = 'date';
                    break;
            }

            // Email field
            if (Str::contains($column->Field, 'email')) {
                $ruleArray[] = 'email';
            }

            // Unique check
            if ($column->Key === 'UNI') {
                $ruleArray[] = "unique:{$this->table},{$column->Field}";
            }

            $rules[] = "'{$column->Field}' => '" . implode('|', $ruleArray) . "'";
        }

        return implode(",\n            ", $rules);
    }

    /**
     * Generate attribute names for validation.
     *
     * @return string
     */
    protected function generateAttributeNames()
    {
        $attributes = [];
        
        foreach ($this->getColumns() as $column) {
            if (in_array($column->Field, $this->unwantedColumns)) {
                continue;
            }
            
            $label = ucwords(str_replace('_', ' ', $column->Field));
            $attributes[] = "'{$column->Field}' => '{$label}'";
        }

        return implode(",\n            ", $attributes);
    }

    /**
     * Generate custom validation messages.
     *
     * @return string
     */
    protected function generateCustomMessages()
    {
        return "// Add custom messages here\n            // 'field.required' => 'The :attribute field is required.'";
    }

    /**
     * Generate table headers for index view.
     *
     * @return string
     */
    protected function generateTableHeaders()
    {
        $headers = [];
        
        foreach ($this->getFilteredColumns() as $column) {
            $label = ucwords(str_replace('_', ' ', $column));
            $headers[] = "<th>{$label}</th>";
        }

        return implode("\n                                        ", $headers);
    }

    /**
     * Generate table columns for index view.
     *
     * @return string
     */
    protected function generateTableColumns()
    {
        $columns = [];
        
        foreach ($this->getFilteredColumns() as $column) {
            $columns[] = "<td>{{ \$" . $this->getModelNameLowerCase() . "->{$column} }}</td>";
        }

        return implode("\n                                            ", $columns);
    }

    /**
     * Generate form fields for create view.
     *
     * @return string
     */
    protected function generateFormFields()
    {
        $fields = [];
        
        foreach ($this->getColumns() as $column) {
            if (in_array($column->Field, $this->unwantedColumns)) {
                continue;
            }

            $fields[] = $this->generateFormField($column, false);
        }

        return implode("\n\n                            ", $fields);
    }

    /**
     * Generate form fields for edit view.
     *
     * @return string
     */
    protected function generateEditFormFields()
    {
        $fields = [];
        
        foreach ($this->getColumns() as $column) {
            if (in_array($column->Field, $this->unwantedColumns)) {
                continue;
            }

            $fields[] = $this->generateFormField($column, true);
        }

        return implode("\n\n                            ", $fields);
    }

    /**
     * Generate individual form field.
     *
     * @param object $column
     * @param bool $isEdit
     * @return string
     */
    protected function generateFormField($column, $isEdit = false)
    {
        $fieldName = $column->Field;
        $label = ucwords(str_replace('_', ' ', $fieldName));
        $type = $this->getColumnType($column->Type);
        $required = $column->Null === 'NO' ? 'required' : '';
        $modelVar = $this->getModelNameLowerCase();
        $oldValue = $isEdit ? "old('{$fieldName}', \${$modelVar}->{$fieldName})" : "old('{$fieldName}')";

        $field = "<div class=\"col-md-6\">\n";
        $field .= "    <label for=\"{$fieldName}\" class=\"form-label\">{$label}";
        
        if ($required) {
            $field .= " <span class=\"text-danger\">*</span>";
        }
        
        $field .= "</label>\n";

        switch ($type) {
            case 'text':
                $field .= "    <textarea class=\"form-control @error('{$fieldName}') is-invalid @enderror\" \n";
                $field .= "              id=\"{$fieldName}\" \n";
                $field .= "              name=\"{$fieldName}\" \n";
                $field .= "              rows=\"3\" \n";
                $field .= "              {$required}>{{ {$oldValue} }}</textarea>\n";
                break;

            case 'boolean':
                $field .= "    <select class=\"form-select @error('{$fieldName}') is-invalid @enderror\" \n";
                $field .= "            id=\"{$fieldName}\" \n";
                $field .= "            name=\"{$fieldName}\" \n";
                $field .= "            {$required}>\n";
                $field .= "        <option value=\"1\" {{ {$oldValue} == 1 ? 'selected' : '' }}>Yes</option>\n";
                $field .= "        <option value=\"0\" {{ {$oldValue} == 0 ? 'selected' : '' }}>No</option>\n";
                $field .= "    </select>\n";
                break;

            case 'date':
                $field .= "    <input type=\"date\" \n";
                $field .= "           class=\"form-control @error('{$fieldName}') is-invalid @enderror\" \n";
                $field .= "           id=\"{$fieldName}\" \n";
                $field .= "           name=\"{$fieldName}\" \n";
                $field .= "           value=\"{{ {$oldValue} }}\" \n";
                $field .= "           {$required}>\n";
                break;

            case 'datetime':
                $field .= "    <input type=\"datetime-local\" \n";
                $field .= "           class=\"form-control @error('{$fieldName}') is-invalid @enderror\" \n";
                $field .= "           id=\"{$fieldName}\" \n";
                $field .= "           name=\"{$fieldName}\" \n";
                $field .= "           value=\"{{ {$oldValue} ? \\Carbon\\Carbon::parse({$oldValue})->format('Y-m-d\\TH:i') : '' }}\" \n";
                $field .= "           {$required}>\n";
                break;

            default:
                $inputType = Str::contains($fieldName, 'email') ? 'email' : 'text';
                $field .= "    <input type=\"{$inputType}\" \n";
                $field .= "           class=\"form-control @error('{$fieldName}') is-invalid @enderror\" \n";
                $field .= "           id=\"{$fieldName}\" \n";
                $field .= "           name=\"{$fieldName}\" \n";
                $field .= "           value=\"{{ {$oldValue} }}\" \n";
                $field .= "           {$required}>\n";
        }

        $field .= "    @error('{$fieldName}')\n";
        $field .= "        <div class=\"invalid-feedback\">{{ \$message }}</div>\n";
        $field .= "    @enderror\n";
        $field .= "</div>";

        return $field;
    }

    /**
     * Generate show fields for detail view.
     *
     * @return string
     */
    protected function generateShowFields()
    {
        $fields = [];
        $modelVar = $this->getModelNameLowerCase();
        
        foreach ($this->getColumns() as $column) {
            if (in_array($column->Field, $this->unwantedColumns)) {
                continue;
            }

            $fieldName = $column->Field;
            $label = ucwords(str_replace('_', ' ', $fieldName));
            
            $field = "<div class=\"col-md-6 mb-3\">\n";
            $field .= "    <div class=\"detail-label\">{$label}</div>\n";
            $field .= "    <div class=\"detail-value\">{{ \${$modelVar}->{$fieldName} ?? 'N/A' }}</div>\n";
            $field .= "</div>";
            
            $fields[] = $field;
        }

        return implode("\n                        ", $fields);
    }

    /**
     * Generate related sections for show view.
     *
     * @return string
     */
    protected function generateRelatedSections()
    {
        return "<!-- Add related records sections here if needed -->";
    }

    /**
     * Generate PDF table headers.
     *
     * @return string
     */
    protected function generatePdfTableHeaders()
    {
        $headers = [];
        
        foreach ($this->getFilteredColumns() as $column) {
            $label = ucwords(str_replace('_', ' ', $column));
            $headers[] = "<th>{$label}</th>";
        }

        return implode("\n                ", $headers);
    }

    /**
     * Generate PDF table columns.
     *
     * @return string
     */
    protected function generatePdfTableColumns()
    {
        $columns = [];
        $modelVar = $this->getModelNameLowerCase();
        
        foreach ($this->getFilteredColumns() as $column) {
            $columns[] = "<td>{{ \${$modelVar}->{$column} }}</td>";
        }

        return implode("\n                    ", $columns);
    }

    /**
     * Get column type from MySQL type.
     *
     * @param string $mysqlType
     * @return string
     */
    protected function getColumnType($mysqlType)
    {
        if (Str::contains($mysqlType, 'int')) {
            return 'integer';
        } elseif (Str::contains($mysqlType, ['varchar', 'char'])) {
            return 'string';
        } elseif (Str::contains($mysqlType, 'text')) {
            return 'text';
        } elseif (Str::contains($mysqlType, ['decimal', 'float', 'double'])) {
            return 'decimal';
        } elseif (Str::contains($mysqlType, 'tinyint(1)')) {
            return 'boolean';
        } elseif (Str::contains($mysqlType, 'date') && !Str::contains($mysqlType, 'datetime')) {
            return 'date';
        } elseif (Str::contains($mysqlType, ['datetime', 'timestamp'])) {
            return 'datetime';
        }

        return 'string';
    }

    /**
     * Get model name in lowercase.
     *
     * @return string
     */
    protected function getModelNameLowerCase()
    {
        return Str::camel($this->name);
    }

    /**
     * Build stub with replacements using parent method.
     *
     * @param string $stub
     * @param array $additionalReplacements
     * @return string
     */
    protected function buildStub($stub, $additionalReplacements = [])
    {
        $modelReplacements = $this->modelReplacements();
        $replacements = array_merge($modelReplacements, $additionalReplacements);

        foreach ($replacements as $key => $value) {
            $stub = str_replace("{{{$key}}}", $value, $stub);
        }

        return $stub;
    }
}
