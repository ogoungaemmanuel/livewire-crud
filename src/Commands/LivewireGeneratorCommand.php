<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Commands;

use Xslainadmin\LivewireCrud\Contracts\CrudGeneratorInterface;
use Xslainadmin\LivewireCrud\ModelGenerator;
use Xslainadmin\LivewireCrud\Support\StubTokens;
use Xslainadmin\LivewireCrud\Support\TypeMapper;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class GeneratorCommand.
 */
abstract class LivewireGeneratorCommand extends Command implements CrudGeneratorInterface
{
    /** The filesystem instance. */
    protected Filesystem $files;

    /**
     * Columns excluded from $fillable and view scaffolding.
     *
     * @var array<string>
     */
    protected array $unwantedColumns = [
        'id',
        'password',
        'email_verified_at',
        'remember_token',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected ?string $table = null;
    protected ?string $modelName = null;
    protected ?string $module = null;
    protected ?string $theme  = null;
    protected string  $themeNamespace = 'Yes';
    protected ?string $menu   = null;
    protected ?string $moduleconvert = null;

    /** @var array<object>|null Cached column metadata; may be pre-loaded via setColumns(). */
    protected ?array $tableColumns = null;

    /**
     * Pre-load column metadata so that getColumns() does not query the DB.
     * Useful for commands that generate CRUD for tables that do not yet exist.
     *
     * Each element must expose Field, Type, Null, Default, Extra properties.
     *
     * @param  array<object>  $columns
     */
    protected function setColumns(array $columns): void
    {
        $this->tableColumns = $columns;
    }

    protected string $modelNamespace = 'App\\Models';

    protected string $templateName = 'backend';

    /** Cached TypeMapper instance. */
    private TypeMapper $typeMapper;

    protected function typeMapper(): TypeMapper
    {
        return $this->typeMapper ??= new TypeMapper();
    }

    /**
     * Model Namespace.
     * @var string
     */
    protected function moduleNamespace()
    {
        $module = trim($this->getModuleInput(), '{}');
        $path = "Modules/" . $module . "/Livewire";
        // $path = "Modules\{$module}\Livewire";
        return trim($path, '{}');
    }

    // protected $moduleNamespace = 'Modules\Backend\Livewire';

    protected string $controllerNamespace = 'App\\Http\\Controllers';
    protected string $livewireNamespace   = 'App\\Http\\Livewire';
    protected string $layout              = 'layouts.app';

    /** @var array<string, mixed> */
    protected array $options = [];

    /**
     * Create a new controller creator command instance.
     * @param \Illuminate\Filesystem\Filesystem $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
        $this->unwantedColumns = config('livewire-crud.model.unwantedColumns', $this->unwantedColumns);
        $this->modelNamespace = config('crud.model.namespace', $this->modelNamespace);
        $this->controllerNamespace = config('livewire-crud.controller.namespace', $this->controllerNamespace);
        $this->livewireNamespace = config('livewire-crud.livewire.namespace', $this->livewireNamespace);
        // $this->moduleNamespace = config('livewire-crud.module.namespace', $this->moduleNamespace());
        $this->layout = config('livewire-crud.layout', $this->layout);
    }

    /**
     * Generate the Model.
     * @return $this
     */
    abstract public function buildModel(): static;

    /**
     * Generate the Model.
     * @return $this
     */
    // abstract protected function buildCreate();

    /**
     * Generate the Model.
     * @return $this
     */
    // abstract protected function buildEdit();
    /**
     * Generate the Model.
     * @return $this
     */
    // abstract protected function buildShow();
    /**
     * Generate the views.
     * @return $this
     */
    abstract public function buildViews(): static;

    /**
     * Build the directory for the given path if it does not exist.
     */
    protected function makeDirectory(string $path): string
    {
        if (!$this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0755, true, true);
        }

        return $path;
    }

    /**
     * Write (overwrite) a file at the given path, creating dirs as needed.
     */
    protected function write(string $path, string $content): void
    {
        $this->files->makeDirectory(dirname($path), 0755, true, true);
        $this->files->put($path, $content);
    }

    /**
     * Get the contents (or path) of a named stub file.
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function getStub(string $type, bool $content = true): string
    {
        $stub_path = config('livewire-crud.stub_path', 'default');
        if ($stub_path == 'default') {
            $stub_path = __DIR__ . '/../stubs/';
        }

        $path = Str::finish($stub_path, '/') . "{$type}.stub";

        if (!$content) {
            return $path;
        }

        return $this->files->get($path);
    }

    /**
     * @param $no
     * @return string
     */
    private function _getSpace($no = 1)
    {
        $tabs = '';
        for ($i = 0; $i < $no; $i++) {
            $tabs .= "\t";
        }

        return $tabs;
    }

    /**
     * @param $name
     * @return string
     */
    protected function _getMigrationPath($name)
    {
        $name = Str::ucfirst($this->modelName);
        $module = $this->getModuleInput();
        // $modulelocation = $this->modelNamespace;
        $path = base_path("/Modules/{$module}/Database/Migrations/" . date('Y-m-d_His') . "_create_" . Str::lower(Str::plural($name)) . "_table.php");
        if (File::exists($path)) {
            File::delete($path);
        }
        return $this->makeDirectory($path);
        // return base_path("database/migrations/" . date('Y-m-d_His') . "_create_" . Str::lower(Str::plural($name)) . "_table.php");
    }
    protected function _getFactoryPath($name)
    {
        $name = Str::ucfirst($this->modelName);
        $module = $this->getModuleInput();
        // $modulelocation = $this->modelNamespace;
        $path = base_path("/Modules/{$module}/Database/factories/{$name}Factory.php");
        if (File::exists($path)) {
            File::delete($path);
        }
        return $this->makeDirectory($path);
    }

    /**
     * @param $name
     * @return string
     */
    protected function _getLivewirePath($name)
    {
		$name = Str::plural($this->modelName);
        return app_path($this->_getNamespacePath($this->livewireNamespace) . "{$name}.php");
    }

    protected function _getModulePath($name)
    {
		$name = Str::plural($this->modelName);
        return base_path($this->_getModuleNamespacePath($this->moduleNamespace()) . "{$name}.php");
        // return base_path($this->_getModuleNamespacePath($this->moduleNamespace) . "{$name}.php");
    }

    // protected function _getModulePath($name)
    // {
	//		$name = Str::plural($this->modelName);
    //     return base_path($this->_getModuleNamespacePath($this->moduleNamespace) . "{$name}.php");
    // }

    /**
     * @param $name
     * @return string
     */
    protected function _getModelPath($name)
    {
        return $this->makeDirectory(app_path($this->_getNamespacePath($this->modelNamespace) . "{$name}.php"));
    }

	protected function _getCreateModelPath($name)
    {
        $name = Str::ucfirst($this->modelName);
        $module = $this->getModuleInput();
        // $modulelocation = $this->modelNamespace;
        $path = base_path("/Modules/{$module}/Models/{$name}.php");
        return $this->makeDirectory($path);
    }

    protected function _getImportPath($name)
    {
        $name = Str::ucfirst(Str::plural($this->modelName));
        $module = $this->getModuleInput();
        // $modulelocation = $this->modelNamespace;
        $path = base_path("/Modules/{$module}/Imports/{$name}Import.php");
        if (File::exists($path)) {
            File::delete($path);
        }
        return $this->makeDirectory($path);
    }

    protected function _getExportPath($name)
    {
        $name = Str::ucfirst(Str::plural($this->modelName));
        $module = $this->getModuleInput();
        // $modulelocation = $this->modelNamespace;
        $path = base_path("/Modules/{$module}/Exports/{$name}Export.php");
        if (File::exists($path)) {
            File::delete($path);
        }
        return $this->makeDirectory($path);
    }

    protected function _getPrintPath($name)
    {
        $name = Str::ucfirst(Str::plural($this->modelName));
        $module = $this->getModuleInput();
        // $modulelocation = $this->modelNamespace;
        $path = base_path("/Modules/{$module}/Exports/{$name}Print.php");
        if (File::exists($path)) {
            File::delete($path);
        }
        return $this->makeDirectory($path);
    }

    protected function _getNotificationPath($name)
    {
        $name = Str::ucfirst($this->modelName);
        $module = $this->getModuleInput();
        // $modulelocation = $this->modelNamespace;
        $path = base_path("/Modules/{$module}/Notifications/{$name}Notification.php");
        if (File::exists($path)) {
            File::delete($path);
        }
        return $this->makeDirectory($path);
    }

    protected function _getEmailPath($name)
    {
        $name = Str::ucfirst($this->modelName);
        $module = $this->getModuleInput();
        // $modulelocation = $this->modelNamespace;
        $path = base_path("/Modules/{$module}/Emails/{$name}Email.php");
        if (File::exists($path)) {
            File::delete($path);
        }
        return $this->makeDirectory($path);
    }

    protected function _getChartPath($name)
    {
        $name = Str::ucfirst(Str::plural($this->modelName));
        $module = $this->getModuleInput();
        // $modulelocation = $this->modelNamespace;
        $path = base_path("/Modules/{$module}/Charts/{$name}Chart.php");
        if (File::exists($path)) {
            File::delete($path);
        }
        return $this->makeDirectory($path);
    }

    protected function _getUploadPath($name)
    {
        $name = Str::ucfirst(Str::plural($this->modelName));
        $module = $this->getModuleInput();
        // $modulelocation = $this->modelNamespace;
        $path = base_path("/Modules/{$module}/Http/Controllers/{$name}Controller.php");
        if (File::exists($path)) {
            File::delete($path);
        }
        return $this->makeDirectory($path);
    }

    protected function _getFullcalendarPath($name)
    {
        $name = Str::ucfirst(Str::plural($this->modelName));
        $module = $this->getModuleInput();
        // $modulelocation = $this->modelNamespace;
        $path = base_path("/Modules/{$module}/Fullcalendar/{$name}Fullcalendar.php");
        if (File::exists($path)) {
            File::delete($path);
        }
        return $this->makeDirectory($path);
    }

    protected function _getPdfExportPath($name)
    {
        $name = Str::ucfirst(Str::plural($this->modelName));
        $module = $this->getModuleInput();
        // $modulelocation = $this->modelNamespace;
        $path = base_path("/Modules/{$module}/Exports/{$name}PdfExport.php");
        if (File::exists($path)) {
            File::delete($path);
        }
        return $this->makeDirectory($path);
    }

    // ── Enterprise path helpers ───────────────────────────────────────────

    /**
     * Modules/{Module}/Policies/{Model}Policy.php
     */
    protected function _getPolicyPath(string $name): string
    {
        $name   = Str::ucfirst($this->modelName);
        $module = $this->getModuleInput();
        $path   = base_path("/Modules/{$module}/Policies/{$name}Policy.php");
        if (File::exists($path)) {
            File::delete($path);
        }
        return $this->makeDirectory($path);
    }

    /**
     * Modules/{Module}/Observers/{Model}Observer.php
     */
    protected function _getObserverPath(string $name): string
    {
        $name   = Str::ucfirst($this->modelName);
        $module = $this->getModuleInput();
        $path   = base_path("/Modules/{$module}/Observers/{$name}Observer.php");
        if (File::exists($path)) {
            File::delete($path);
        }
        return $this->makeDirectory($path);
    }

    /**
     * Modules/{Module}/Services/{Model}Service.php
     */
    protected function _getServicePath(string $name): string
    {
        $name   = Str::ucfirst($this->modelName);
        $module = $this->getModuleInput();
        $path   = base_path("/Modules/{$module}/Services/{$name}Service.php");
        if (File::exists($path)) {
            File::delete($path);
        }
        return $this->makeDirectory($path);
    }

    /**
     * Modules/{Module}/Http/Controllers/Api/{Model}Controller.php
     */
    protected function _getApiControllerPath(string $name): string
    {
        $name   = Str::ucfirst($this->modelName);
        $module = $this->getModuleInput();
        $path   = base_path("/Modules/{$module}/Http/Controllers/Api/{$name}Controller.php");
        if (File::exists($path)) {
            File::delete($path);
        }
        return $this->makeDirectory($path);
    }

    /**
     * Modules/{Module}/Http/Resources/{Model}Resource.php
     */
    protected function _getApiResourcePath(string $name): string
    {
        $name   = Str::ucfirst($this->modelName);
        $module = $this->getModuleInput();
        $path   = base_path("/Modules/{$module}/Http/Resources/{$name}Resource.php");
        if (File::exists($path)) {
            File::delete($path);
        }
        return $this->makeDirectory($path);
    }

    /**
     * Modules/{Module}/Database/Seeders/{Model}Seeder.php
     */
    protected function _getSeederPath(string $name): string
    {
        $name   = Str::ucfirst($this->modelName);
        $module = $this->getModuleInput();
        $path   = base_path("/Modules/{$module}/Database/Seeders/{$name}Seeder.php");
        if (File::exists($path)) {
            File::delete($path);
        }
        return $this->makeDirectory($path);
    }

    /**
     * Modules/{Module}/Tests/Feature/{Model}FeatureTest.php
     */
    protected function _getFeatureTestPath(string $name): string
    {
        $name   = Str::ucfirst($this->modelName);
        $module = $this->getModuleInput();
        $path   = base_path("/Modules/{$module}/Tests/Feature/{$name}FeatureTest.php");
        if (File::exists($path)) {
            File::delete($path);
        }
        return $this->makeDirectory($path);
    }

    /**
     * @param $name
     * @return string
     */

    // protected function _getCreatePath($name)
    // {
    //     return $this->makeDirectory(app_path($this->_getNamespacePath($this->modelNamespace) . "{$name}.php"));
    // }

    /**
     * Get the path from namespace.
     * @param $namespace
     * @return string
     */
    private function _getNamespacePath($namespace)
    {
        $str = Str::start(Str::finish(Str::after($namespace, 'App'), '\\'), '\\');

        return str_replace('\\', '/', $str);
    }

    private function _getModuleNamespacePath($namespace)
    {
        $str = Str::start(Str::finish(Str::after($namespace, 'modules'), '\\'), '\\');

        return str_replace('\\', '/', $str);
    }

    /**
     * Get the default layout path.
     * @return string
     */
    private function _getLayoutPath()
    {
        return $this->makeDirectory(resource_path("/views/layouts/app.blade.php"));
    }

    /**
     * @param $view
     * @return string
     */
    protected function _getViewPath($view)
    {
        $name = Str::kebab(Str::plural($this->modelName));
        $module = $this->getModuleInput();
        $theme = $this->getThemeInput();
        $modulelocation = $this->modelNamespace;
        if ($theme == 'none') {
            return $this->makeDirectory(base_path("/Modules/{$module}/resources/views/livewire/{$name}/{$view}.blade.php"));
        } else {
            return $this->makeDirectory(base_path("/Modules/{$module}/resources/views/livewire/{$name}/{$view}.blade.php"));
            // return $this->makeDirectory(base_path("/Modules/{$module}/resources/views/livewire/{$theme}/{$name}/{$view}.blade.php"));
        }

    }

    /**
     * Single-file component (Volt) path.
     * Produces: Modules/{Module}/resources/views/livewire/{plural-kebab}.blade.php
     */
    protected function _getVoltComponentPath(): string
    {
        $name   = Str::kebab(Str::plural($this->modelName));
        $module = $this->getModuleInput();
        return $this->makeDirectory(base_path("/Modules/{$module}/resources/views/livewire/{$name}.blade.php"));
    }

    protected function _getCreatePath($name)
    {
        $name = Str::ucfirst(Str::plural($this->modelName));
        $module = $this->getModuleInput();
        $modulelocation = $this->modelNamespace;
        $path = base_path("/Modules/{$module}/livewire/{$name}/create.php");
        if (File::exists($path)) {
            File::delete($path);
        }
        return $this->makeDirectory($path);
    }

    protected function _getEditPath($name)
    {
        $name = Str::ucfirst(Str::plural($this->modelName));
        $module = $this->getModuleInput();
        $modulelocation = $this->modelNamespace;
        $path = base_path("Modules/{$module}/livewire/{$name}/edit.php");
        if (File::exists($path)) {
            File::delete($path);
        }
        return $this->makeDirectory($path);
    }

    protected function _getShowPath($name)
    {
        $name = Str::ucfirst(Str::plural($this->modelName));
        $module = $this->getModuleInput();
        $modulelocation = $this->modelNamespace;
        $path = base_path("/Modules/{$module}/livewire/{$name}/show.php");
        if (File::exists($path)) {
            File::delete($path);
        }
        return $this->makeDirectory($path);
    }

    protected function _getDeletePath($name)
    {
        $name = Str::ucfirst(Str::plural($this->modelName));
        $module = $this->getModuleInput();
        $modulelocation = $this->modelNamespace;
        $path = base_path("/Modules/{$module}/livewire/{$name}/delete.php");
        if (File::exists($path)) {
            File::delete($path);
        }
        return $this->makeDirectory($path);
    }

    protected function _getResourcePath(): string
    {
        $name   = Str::ucfirst($this->modelName);
        $module = $this->getModuleInput();
        $path   = base_path("/Modules/{$module}/Resources/{$name}Resource.php");
        return $this->makeDirectory($path);
    }

    // -----------------------------------------------------------------------
    // Migration generation
    // -----------------------------------------------------------------------

    /**
     * Build the Blueprint column definitions for the migration stub.
     *
     * Skips: id, created_at, updated_at, remember_token.
     * Appends $table->softDeletes() automatically when deleted_at is present.
     *
     * @return string  Multi-line string, each line indented with 12 spaces.
     */
    protected function buildMigrationColumns(): string
    {
        $lines      = [];
        $hasSoftDel = false;
        $pad        = '            ';   // 12 spaces (aligns inside Schema::create closure)
        $mapper     = $this->typeMapper();

        $skip = ['id', 'created_at', 'updated_at', 'remember_token'];

        foreach ($this->getColumns() as $col) {
            $field   = $col->Field   ?? '';
            $type    = $col->Type    ?? 'varchar(255)';
            $null    = ($col->Null   ?? 'YES') === 'YES';
            $default = $col->Default ?? null;
            $extra   = strtolower($col->Extra ?? '');

            // Skip auto-managed columns
            if (in_array($field, $skip, true) || str_contains($extra, 'auto_increment')) {
                continue;
            }

            // Soft-deletes: capture and append at the end
            if ($field === 'deleted_at') {
                $hasSoftDel = true;
                continue;
            }

            $def     = $mapper->migrationColumnDefinition($field, $type, $null, $default);
            $lines[] = $pad . $def . ';';
        }

        if ($hasSoftDel) {
            $lines[] = $pad . "\$table->softDeletes();";
        }

        return implode("\n", $lines);
    }

    /**
     * Generate a Laravel migration file for the current table.
     *
     * The file is written to Modules/{module}/Database/Migrations/.
     * If a migration for this table already exists it is deleted first
     * (handled by _getMigrationPath).
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function buildMigration(): static
    {
        $migrationPath = $this->_getMigrationPath($this->modelName);

        $replace = array_merge($this->buildReplacements(), [
            StubTokens::MIGRATION_COLUMNS => $this->buildMigrationColumns(),
        ]);

        $migrationTemplate = str_replace(
            array_keys($replace),
            array_values($replace),
            $this->getStub('Migration')
        );

        $this->warn('Creating: <info>Migration...</info>');
        $this->write($migrationPath, $migrationTemplate);

        return $this;
    }

    // -----------------------------------------------------------------------
    // Resource token builders (used by modelReplacements)
    // -----------------------------------------------------------------------

    /**
     * Generate the {{resourceColumns}} token: one TextColumn / DateColumn / etc.
     * line per DB column, indented for the Resource stub.
     */
    protected function buildResourceColumns(): string
    {
        $lines  = [];
        $pad    = '                ';   // 16 spaces
        $mapper = $this->typeMapper();

        foreach ($this->getColumns() as $col) {
            $field = $col->Field;
            if (in_array($field, ['id', 'created_at', 'updated_at', 'deleted_at', 'remember_token'])) {
                continue;
            }
            $type   = $col->Type ?? '';
            $title  = Str::title(str_replace('_', ' ', $field));
            $class  = $mapper->columnClass($field, $type);
            $chain  = $mapper->columnChain($field, $type);
            $lines[] = $pad . "{$class}::make('{$field}')->label('{$title}'){$chain},";
        }

        // Always append created_at
        $lines[] = $pad . "DateColumn::make('created_at')->label('Created')->dateTime()->sortable(),";

        return implode("\n", $lines);
    }

    /**
     * Generate the {{resourceFormFields}} token.
     */
    protected function buildResourceFormFields(): string
    {
        $lines  = [];
        $pad    = '            ';  // 12 spaces
        $mapper = $this->typeMapper();

        foreach ($this->getColumns() as $col) {
            $field   = $col->Field;
            if (in_array($field, ['id', 'created_at', 'updated_at', 'deleted_at', 'remember_token'])) {
                continue;
            }
            $type    = $col->Type ?? '';
            $title   = Str::title(str_replace('_', ' ', $field));
            $req     = ($col->Null === 'NO');
            $class   = $mapper->fieldClass($field, $type);
            $chain   = $mapper->fieldChain($field, $type, $req);
            $lines[] = $pad . "{$class}::make('{$field}')->label('{$title}'){$chain},";
        }

        return implode("\n", $lines);
    }

    /**
     * Generate the {{resourceFilters}} token.
     */
    protected function buildResourceFilters(): string
    {
        $lines  = [];
        $pad    = '                ';   // 16 spaces
        $mapper = $this->typeMapper();

        foreach ($this->getColumns() as $col) {
            $field = $col->Field;
            $type  = $col->Type ?? '';
            $title = Str::title(str_replace('_', ' ', $field));

            // Ternary filter for boolean-like columns
            if (in_array($mapper->fieldClass($field, $type), ['Toggle'])) {
                $lines[] = $pad . "TernaryFilter::make('{$field}')->label('{$title}'),";
                continue;
            }

            // Select filter for status/type enum-like columns
            if (str_contains($field, 'status') || str_contains($field, 'type') || str_contains(strtolower($type), 'enum')) {
                $lines[] = $pad . "SelectFilter::make('{$field}')->label('{$title}'),";
            }
        }

        // Always add a date filter on created_at
        $lines[] = $pad . "DateFilter::make('created_at')->label('Date Range'),";

        return implode("\n", $lines);
    }

    /**
     * Build the common stub token → value replacement map.
     *
     * @return array<string, string>
     */
    protected function buildReplacements(): array
    {
        return [
            StubTokens::GET_TEMPLATE                       => $this->templateName,
            StubTokens::GET_NAME_INPUT                     => $this->getNameInput(),
            StubTokens::GET_NAME_INPUT_LOWER               => Str::lower($this->getNameInput()),
            StubTokens::GET_NAME_INPUT_PLURAL_LOWER        => Str::lower(Str::plural($this->getNameInput())),
            StubTokens::GET_THEME                          => $this->getThemeInput(),
            StubTokens::GET_THEME_INPUT                    => $this->getThemeInput(),
            StubTokens::GET_THEME_INPUT_LOWER              => Str::lower($this->getThemeInput()),
            StubTokens::THEME_LOWER                        => Str::lower($this->getThemeInput()),
            StubTokens::GET_MODULE_INPUT_MODULE            => $this->getModuleInput(),
            StubTokens::GET_MODULE_INPUT_MODULE_NEW        => ucfirst($this->getModuleInput()),
            StubTokens::GET_MODULE_INPUT                   => Str::lower($this->getModuleInput()),
            StubTokens::GET_MODULE_INPUT_LOWER             => Str::lower($this->getModuleInput()),
            StubTokens::GET_MODULE_INPUT_MODULE_LOWER_CASE => Str::lower($this->getModuleInput()),
            StubTokens::GET_MODULE_INPUT_CLASS             => Str::studly($this->getModuleInput()),
            StubTokens::GET_MODULE_INPUT_TITLE             => Str::title($this->getModuleInput()),
            StubTokens::LAYOUT                             => $this->layout,
            StubTokens::MODEL_NAME                         => $this->modelName,
            StubTokens::MODEL_NAME_SINGULAR_LOWER          => Str::lower($this->modelName),
            StubTokens::MODEL_PLURAL_NAME                  => Str::plural($this->modelName),
            StubTokens::MODEL_TITLE                        => Str::title(Str::snake($this->modelName, ' ')),
            StubTokens::MODEL_PLURAL_TITLE                 => Str::title(Str::snake(Str::plural($this->modelName), ' ')),
            StubTokens::MODEL_NAMESPACE                    => $this->modelNamespace,
            StubTokens::CONTROLLER_NAMESPACE               => $this->controllerNamespace,
            StubTokens::MODEL_NAME_PLURAL_LOWER            => Str::camel(Str::plural($this->modelName)),
            StubTokens::MODEL_NAME_PLURAL_UPPER            => ucfirst(Str::plural($this->modelName)),
            StubTokens::MODEL_NAME_LOWER                   => Str::camel($this->modelName),
            StubTokens::MODEL_ROUTE                        => $this->options['route'] ?? Str::kebab(Str::plural($this->modelName)),
            StubTokens::MODEL_VIEW                         => Str::kebab($this->modelName),
        ];
    }

    /**
     * Build the form fields for form.
     * @param $title
     * @param $column
     * @param string $type
     * @return mixed
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     *
     */
    protected function getField($title, $column, $type = 'form-field')
    {
        $replace = array_merge($this->buildReplacements(), [
            StubTokens::TITLE  => $title,
            StubTokens::COLUMN => $column,
        ]);

        return str_replace(
            array_keys($replace),
            array_values($replace),
            $this->getStub("views/{$type}")
        );
    }

    protected function getDataField($title, $column, $datatype)
    {
        $replace = array_merge($this->buildReplacements(), [
            StubTokens::TITLE    => $title,
            StubTokens::COLUMN   => $column,
            StubTokens::DATATYPE => $datatype,
        ]);

        return str_replace(
            array_keys($replace),
            array_values($replace),
            $this->getStub("views/datatype/{$datatype}")
        );
    }
    /**
     * Build the form fields for form.
     * @param $title
     * @param $column
     * @param string $type
     * @return mixed
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     *
     */
    protected function showField($title, $column, $type = 'show-field')
    {
        $replace = array_merge($this->buildReplacements(), [
            StubTokens::TITLE  => $title,
            StubTokens::COLUMN => $column,
        ]);

        return str_replace(
            array_keys($replace),
            array_values($replace),
            $this->getStub("views/{$type}")
        );
    }

    /**
     * @param $title
     * @return mixed
     */
    protected function getHead($title)
    {
        $replace = array_merge($this->buildReplacements(), [
            StubTokens::TITLE => $title,
        ]);

        return str_replace(
            array_keys($replace),
            array_values($replace),
            $this->_getSpace(4) . '<th>' . StubTokens::TITLE . '</th>' . "\n"
        );
    }

    /**
     * @param $column
     * @return mixed
     */
    protected function getBody($column)
    {
        $replace = array_merge($this->buildReplacements(), [
            StubTokens::COLUMN => $column,
        ]);

        return str_replace(
            array_keys($replace),
            array_values($replace),
            $this->_getSpace(4) . '<td>{{ $row->' . StubTokens::COLUMN . ' }}</td>' . "\n"
        );
    }

    /**
     * Make layout if not exists.
     * @throws \Exception
     */
    protected function buildLayout(): void
    {
        if (!(view()->exists($this->layout))) {

            $this->info('Creating Layout ...');

            if ($this->layout == 'layouts.app') {
                $this->files->copy($this->getStub('layouts/app', false), $this->_getLayoutPath());
            } else {
                throw new \Exception("{$this->layout} layout not found!");
            }
        }
    }

    /**
     * Get the DB table columns.
     * Supports MySQL, PostgreSQL, SQLite, and SQL Server.
     *
     * @return array<object>
     *
     * @throws \Exception
     */
    protected function getColumns(): array
    {
        if (empty($this->tableColumns)) {
            $driver = DB::getDriverName();

            try {
                switch ($driver) {
                    case 'mysql':
                        $result = DB::select('SHOW COLUMNS FROM `' . $this->table . '`');
                        // Ensure we're working with an array
                        $this->tableColumns = is_array($result) ? $result : [$result];
                        break;

                    case 'pgsql':
                        $result = DB::select(
                            "SELECT column_name as Field, data_type as Type, is_nullable as Null, column_default as Default
                             FROM information_schema.columns
                             WHERE table_name = ?
                             ORDER BY ordinal_position",
                            [$this->table]
                        );
                        $this->tableColumns = is_array($result) ? $result : [$result];
                        break;

                    case 'sqlite':
                        $columns = DB::select("PRAGMA table_info(`{$this->table}`)");
                        $this->tableColumns = collect($columns)->map(function ($column) {
                            return (object) [
                                'Field' => $column->name,
                                'Type' => $column->type,
                                'Null' => $column->notnull ? 'NO' : 'YES',
                                'Default' => $column->dflt_value,
                            ];
                        })->toArray();
                        break;

                    case 'sqlsrv':
                        $result = DB::select(
                            "SELECT COLUMN_NAME as Field, DATA_TYPE as Type, IS_NULLABLE as Null, COLUMN_DEFAULT as Default
                             FROM INFORMATION_SCHEMA.COLUMNS
                             WHERE TABLE_NAME = ?
                             ORDER BY ORDINAL_POSITION",
                            [$this->table]
                        );
                        $this->tableColumns = is_array($result) ? $result : [$result];
                        break;

                    default:
                        // Fallback: Use Laravel's Schema facade
                        $columns = Schema::getColumnListing($this->table);
                        $this->tableColumns = collect($columns)->map(function ($column) {
                            $type = Schema::getColumnType($this->table, $column);
                            return (object) [
                                'Field' => $column,
                                'Type' => $type,
                                'Null' => 'YES',
                                'Default' => null,
                            ];
                        })->toArray();
                        break;
                }

                // Debug output
                if (method_exists($this, 'info')) {
                    $this->info("getColumns() returned " . count($this->tableColumns) . " columns");
                }
            } catch (\Exception $e) {
                throw new \Exception("Failed to get columns from table '{$this->table}': " . $e->getMessage());
            }
        }

        return $this->tableColumns;
    }

    /**
     * Return column names after stripping unwanted system columns.
     *
     * @return array<string>
     */
    protected function getFilteredColumns(): array
    {
        $unwanted = $this->unwantedColumns;
        $columns = [];

        foreach ($this->getColumns() as $column) {
            $columns[] = $column->Field;
        }

        return array_filter($columns, function ($value) use ($unwanted) {
            return !in_array($value, $unwanted);
        });
    }

    /**
     * Build model-specific stub replacements.
     *
     * @return array<string, string>
     */
    protected function modelReplacements(): array
    {
        $properties = '';
        $rulesArray = [];
        $softDeletesNamespace = $softDeletes = '';

        foreach ($this->getColumns() as $value) {
            $properties .= "\n * @property $$value->Field";

            if ($value->Null == 'NO') {
                $rulesArray[$value->Field] = 'required';
            }

            if ($value->Field == 'deleted_at') {
                $softDeletesNamespace = "use Illuminate\Database\Eloquent\SoftDeletes;\n";
                $softDeletes = "use SoftDeletes;\n";
            }
        }

        $rules = function () use ($rulesArray) {
            $rules = '';
            // Exclude the unwanted rulesArray
            $rulesArray = Arr::except($rulesArray, $this->unwantedColumns);
            // Make rulesArray
            foreach ($rulesArray as $col => $rule) {
                $rules .= "\n\t\t'{$col}' => '{$rule}',";
            }

            return $rules;
        };

        $fillable = function () {

            /** @var array $filterColumns Exclude the unwanted columns */
            $filterColumns = $this->getFilteredColumns();

            // Add quotes to the unwanted columns for fillable
            array_walk($filterColumns, function (&$value) {
                $value = "'" . $value . "'";
            });

            // CSV format
            return implode(',', $filterColumns);
        };

        $updatefield = function () {

            /** @var array $filterColumns Exclude the unwanted columns */
            $filterColumns = $this->getFilteredColumns();

            // Add quotes to the unwanted columns for fillable
            array_walk($filterColumns, function (&$value) {
                $value = "$" . $value . "";
            });

            // CSV format
            return implode(', ', $filterColumns);
        };

        $resetfields = function () {

            /** @var array $filterColumns Exclude the unwanted columns */
            $filterColumns = $this->getFilteredColumns();

            // Add quotes to the unwanted columns for fillable
            array_walk($filterColumns, function (&$value) {
                $value = "\n\t\t\$this->" . $value . " = null";
                $value .= ";";
            });

            // CSV format
            return implode('', $filterColumns);
        };

        $addfields = function () {

            /** @var array $filterColumns Exclude the unwanted columns */
            $filterColumns = $this->getFilteredColumns();

            // Add quotes to the unwanted columns for fillable
            array_walk($filterColumns, function (&$value) {
                $value = "\n\t\t\t'" . $value . "' => \$this-> " . $value;
            });

            // CSV format
            return implode(',', $filterColumns);
        };

        $keyWord = function () {

            /** @var array $filterColumns Exclude the unwanted columns */
            $filterColumns = $this->getFilteredColumns();

            // Add quotes to the unwanted columns for fillable
            array_walk($filterColumns, function (&$value) {
                $value = "\n\t\t\t\t\t\t->orWhere('" . $value . "', 'LIKE', \$keyWord)";
            });

            // CSV format
            return implode('', $filterColumns);
        };

        $factoryfields = function () {

            /** @var array $filterColumns Exclude the unwanted columns */
            $filterColumns = $this->getFilteredColumns();

            // Add quotes to the unwanted columns for fillable */
            array_walk($filterColumns, function (&$value) {
                $value = "\n\t\t\t'" . $value . "' => \$this->faker->name,";
            });

            // CSV format
            return implode('', $filterColumns);
        };

        $showfields = function () {

            /** @var array $filterColumns Exclude the unwanted columns */
            $filterColumns = $this->getFilteredColumns();

            // Add quotes to the unwanted columns for fillable
            array_walk($filterColumns, function (&$value) {
                $value = "\n\t\t\$this->" . $value . " = \$record-> " . $value . ";";
            });

            // CSV format
            return implode('', $filterColumns);
        };

        $fieldsList = function () {

            /** @var array $filterColumns Exclude the unwanted columns */
            $filterColumns = $this->getFilteredColumns();

            // Add quotes to the unwanted columns for fillable
            array_walk($filterColumns, function (&$value) {
                $value = $value;
            });

            // CSV format
            return implode(', ', $filterColumns);
        };

        $editfields = function () {

            /** @var array $filterColumns Exclude the unwanted columns */
            $filterColumns = $this->getFilteredColumns();

            // Add quotes to the unwanted columns for fillable
            array_walk($filterColumns, function (&$value) {
                $value = "\n\t\t\$this->" . $value . " = \$record-> " . $value . ";";
            });

            // CSV format
            return implode('', $filterColumns);
        };

        $templateHeaders = function () {

            /** @var array $filterColumns Exclude the unwanted columns */
            $filterColumns = $this->getFilteredColumns();

            // Add quotes to create CSV header array
            array_walk($filterColumns, function (&$value) {
                $value = "'" . ucwords(str_replace('_', ' ', $value)) . "'";
            });

            // CSV format
            return implode(', ', $filterColumns);
        };

        list($relations, $properties) = (new ModelGenerator($this->table, $properties, $this->modelNamespace))->getEloquentRelations();

        return [
            StubTokens::FILLABLE               => $fillable(),
            StubTokens::UPDATE_FIELD            => $updatefield(),
            StubTokens::RESET_FIELDS            => $resetfields(),
            StubTokens::SHOW_FIELDS             => $showfields(),
            StubTokens::EDIT_FIELDS             => $editfields(),
            StubTokens::FIELDS_LIST             => $fieldsList(),
            StubTokens::ADD_FIELDS              => $addfields(),
            StubTokens::FACTORY                 => $factoryfields(),
            StubTokens::RULES                   => $rules(),
            StubTokens::SEARCH                  => $keyWord(),
            StubTokens::TEMPLATE_HEADERS        => $templateHeaders(),
            StubTokens::RELATIONS               => $relations,
            StubTokens::PROPERTIES              => $properties,
            StubTokens::SOFT_DELETES_NAMESPACE  => $softDeletesNamespace,
            StubTokens::SOFT_DELETES            => $softDeletes,
            StubTokens::RESOURCE_COLUMNS        => $this->buildResourceColumns(),
            StubTokens::RESOURCE_FORM_FIELDS    => $this->buildResourceFormFields(),
            StubTokens::RESOURCE_FILTERS        => $this->buildResourceFilters(),
            StubTokens::NAVIGATION_ICON         => 'bi-table',
            StubTokens::MODEL_NAME_PLURAL_TITLE => Str::title(Str::snake(Str::plural($this->modelName), ' ')),
        ];
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return trim($this->argument('name'));
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getModuleInput()
    {
        return trim($this->argument('module'), '{}');
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getThemeInput()
    {
        return trim($this->argument('theme'), '{}');
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getTemplateInput()
    {
        return trim($this->argument('template'), '{}');
    }

    protected function getRouteInput()
    {
        return trim($this->argument('route'), '{}');
    }

    protected function getLayoutInput()
    {
        return trim($this->argument('layout'), '{}');
    }



    protected function getMenuInput()
    {
        return trim($this->argument('menu'), '{}');
    }
    /**
     * Get the console command arguments.
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the table'],
        ];
    }

    /**
     * Return true when the given table exists in the connected database.
     */
    protected function tableExists(): bool
    {
        return Schema::hasTable($this->table);
    }
}
