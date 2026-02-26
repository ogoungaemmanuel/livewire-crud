<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Support;

/**
 * StubTokenReference — exhaustive cross-reference of every {{token}} used
 * across all stub files in the package.
 *
 * Purpose
 * ───────
 * This file serves as a single source-of-truth for:
 *  1. Which tokens exist and what value they resolve to at runtime.
 *  2. Which stub files consume each token.
 *  3. Which group / category each token belongs to.
 *
 * It does NOT replace StubTokens.php — that class owns the constants and the
 * runtime replacement map.  StubTokenReference is purely a reference / helper
 * for IDE tooling, code generators, and documentation.
 *
 * Quick usage
 * ───────────
 *   // All tokens → their descriptions
 *   StubTokenReference::all();
 *
 *   // Which stubs use a specific token?
 *   StubTokenReference::stubsUsingToken('{{modelName}}');
 *
 *   // Which tokens does a specific stub file use?
 *   StubTokenReference::tokensInStub('Factory.stub');
 *
 *   // Validate a piece of stub content — returns unknown tokens
 *   StubTokenReference::validate($stubString);
 *
 * ┌──────────────────────────────────────────────────────────────────────────────────────────┐
 * │  COMPLETE TOKEN REFERENCE TABLE                                                          │
 * ├───────────────────────────────────────┬───────────────┬─────────────────────────────────┤
 * │  Token                                │  Group        │  Resolved from                  │
 * ├───────────────────────────────────────┼───────────────┼─────────────────────────────────┤
 * │  {{modelName}}                        │  model        │  Studly model name ("Product")   │
 * │  {{modelNameLowerCase}}               │  model        │  camelCase name ("product")      │
 * │  {{modelNameSingularLowerCase}}       │  model        │  Str::lower($name)               │
 * │  {{modelPluralName}}                  │  model        │  Studly plural ("Products")      │
 * │  {{modelTitle}}                       │  model        │  Title snake ("My Model")        │
 * │  {{modelPluralTitle}}                 │  model        │  Title plural ("My Models")      │
 * │  {{modelNamePluralLowerCase}}         │  model        │  camelCase plural ("products")   │
 * │  {{modelNamePluralUpperCase}}         │  model        │  ucfirst plural ("Products")     │
 * │  {{modelNamePluralTitle}}             │  model        │  Title plural (alias)            │
 * │  {{modelRoute}}                       │  model        │  kebab plural URL ("my-models")  │
 * │  {{modelView}}                        │  model        │  kebab singular view name        │
 * │  {{modelNamespace}}                   │  model        │  Model namespace from config     │
 * │  {{controllerNamespace}}              │  model        │  Controller namespace from cfg   │
 * ├───────────────────────────────────────┼───────────────┼─────────────────────────────────┤
 * │  {{getNameInput}}                     │  input        │  Raw table / argument name       │
 * │  {{getNameInputLower}}                │  input        │  Str::lower(table)               │
 * │  {{getNameInputPluralLower}}          │  input        │  Lowercase plural table name     │
 * │  {{getTemplate}}                      │  input        │  $templateName property          │
 * │  {{getTheme}}                         │  input        │  Theme slug ("modern")           │
 * │  {{getThemeInput}}                    │  input        │  Theme slug (alias)              │
 * │  {{getThemeInputLower}}               │  input        │  Str::lower(theme)               │
 * │  {{themelower}}                       │  input        │  Str::lower(theme) (alias)       │
 * │  {{getModuleInput}}                   │  input        │  Str::lower(module) ("shop")     │
 * │  {{getModuleInputModule}}             │  input        │  Raw module name ("Shop")        │
 * │  {{getModuleInputModuleNew}}          │  input        │  ucfirst(module)                 │
 * │  {{getModuleInputTitle}}              │  input        │  Str::title(module)              │
 * │  {{getModuleInputClass}}              │  input        │  Str::studly(module)             │
 * │  {{getModuleInputLower}}              │  input        │  Str::lower(module) (alias)      │
 * │  {{getModuleInputModuleLowerCase}}    │  input        │  Str::lower(module) for views    │
 * │  {{layout}}                           │  input        │  Layout path from config         │
 * ├───────────────────────────────────────┼───────────────┼─────────────────────────────────┤
 * │  {{fillable}}                         │  component    │  $fillable CSV from DB columns   │
 * │  {{updatefield}}                      │  component    │  Public property declarations    │
 * │  {{resetfields}}                      │  component    │  $this->field = null; lines      │
 * │  {{showfields}}                       │  component    │  $this->field = $record-> lines  │
 * │  {{editfields}}                       │  component    │  $this->field = $record-> lines  │
 * │  {{addfields}}                        │  component    │  $record->field = $this-> lines  │
 * │  {{fieldsList}}                       │  component    │  Comma-sep column name list      │
 * │  {{factory}}                          │  component    │  faker->xxx lines per column     │
 * │  {{rules}}                            │  component    │  Validation rule array entries   │
 * │  {{search}}                           │  component    │  ->orWhere() keyword chain       │
 * │  {{templateHeaders}}                  │  component    │  Excel header columns array      │
 * │  {{relations}}                        │  component    │  Eloquent relation methods       │
 * │  {{properties}}                       │  component    │  Extra @property docblock lines  │
 * │  {{softDeletesNamespace}}             │  component    │  SoftDeletes use statement       │
 * │  {{softDeletes}}                      │  component    │  "use SoftDeletes;" or ""        │
 * ├───────────────────────────────────────┼───────────────┼─────────────────────────────────┤
 * │  {{tableHeader}}                      │  view         │  <th> cells per visible column   │
 * │  {{tableBody}}                        │  view         │  <td> cells per visible column   │
 * │  {{viewRows}}                         │  view         │  view-row partials per column    │
 * │  {{form}}                             │  view         │  Datatype-aware form fields      │
 * │  {{show}}                             │  view         │  Show-field partials             │
 * │  {{type}}                             │  view         │  Default input type ("text")     │
 * ├───────────────────────────────────────┼───────────────┼─────────────────────────────────┤
 * │  {{resourceColumns}}                  │  resource     │  Column builder lines            │
 * │  {{resourceFormFields}}               │  resource     │  Field builder lines             │
 * │  {{resourceFilters}}                  │  resource     │  Filter builder lines            │
 * │  {{navigationIcon}}                   │  resource     │  Bootstrap icon class            │
 * ├───────────────────────────────────────┼───────────────┼─────────────────────────────────┤
 * │  {{title}}                            │  field        │  Human-readable column title     │
 * │  {{column}}                           │  field        │  Raw snake_case column name      │
 * │  {{datatype}}                         │  field        │  HTML input type ("text", …)     │
 * └───────────────────────────────────────┴───────────────┴─────────────────────────────────┘
 */
final class StubTokenReference
{
    // -----------------------------------------------------------------------
    // Token-level metadata
    // -----------------------------------------------------------------------

    /**
     * Complete metadata for every token.
     *
     * Keys   : {{token}} strings (matching StubTokens constants)
     * Values : array{group: string, description: string, example: string}
     *
     * @return array<string, array{group: string, description: string, example: string}>
     */
    public static function all(): array
    {
        return [
            // ── Model / class name tokens ──────────────────────────────────
            '{{modelName}}' => [
                'group'       => 'model',
                'description' => 'Studly-case model class name.',
                'example'     => 'Product',
                'constant'    => 'StubTokens::MODEL_NAME',
            ],
            '{{modelNameLowerCase}}' => [
                'group'       => 'model',
                'description' => 'camelCase model name (Str::camel).',
                'example'     => 'product',
                'constant'    => 'StubTokens::MODEL_NAME_LOWER',
            ],
            '{{modelNameSingularLowerCase}}' => [
                'group'       => 'model',
                'description' => 'Str::lower of the model name.',
                'example'     => 'product',
                'constant'    => 'StubTokens::MODEL_NAME_SINGULAR_LOWER',
            ],
            '{{modelPluralName}}' => [
                'group'       => 'model',
                'description' => 'Studly plural model name (Str::plural).',
                'example'     => 'Products',
                'constant'    => 'StubTokens::MODEL_PLURAL_NAME',
            ],
            '{{modelTitle}}' => [
                'group'       => 'model',
                'description' => 'Title-case model name from snake (Str::title(Str::snake($name, " "))).',
                'example'     => 'Sale Order',
                'constant'    => 'StubTokens::MODEL_TITLE',
            ],
            '{{modelPluralTitle}}' => [
                'group'       => 'model',
                'description' => 'Title-case plural model name.',
                'example'     => 'Sale Orders',
                'constant'    => 'StubTokens::MODEL_PLURAL_TITLE',
            ],
            '{{modelNamePluralLowerCase}}' => [
                'group'       => 'model',
                'description' => 'camelCase plural (Str::camel(Str::plural($name))).',
                'example'     => 'products',
                'constant'    => 'StubTokens::MODEL_NAME_PLURAL_LOWER',
            ],
            '{{modelNamePluralUpperCase}}' => [
                'group'       => 'model',
                'description' => 'ucfirst plural (ucfirst(Str::plural($name))).',
                'example'     => 'Products',
                'constant'    => 'StubTokens::MODEL_NAME_PLURAL_UPPER',
            ],
            '{{modelNamePluralTitle}}' => [
                'group'       => 'model',
                'description' => 'Title-case plural (used in Resource stubs).',
                'example'     => 'Sale Orders',
                'constant'    => 'StubTokens::MODEL_NAME_PLURAL_TITLE',
            ],
            '{{modelRoute}}' => [
                'group'       => 'model',
                'description' => 'kebab-case plural URL slug (Str::kebab(Str::plural($name))).',
                'example'     => 'sale-orders',
                'constant'    => 'StubTokens::MODEL_ROUTE',
            ],
            '{{modelView}}' => [
                'group'       => 'model',
                'description' => 'kebab-case singular view name.',
                'example'     => 'sale-order',
                'constant'    => 'StubTokens::MODEL_VIEW',
            ],
            '{{modelNamespace}}' => [
                'group'       => 'model',
                'description' => 'Fully-qualified model namespace from config (default: App\\Models).',
                'example'     => 'Modules\\Shop\\Models',
                'constant'    => 'StubTokens::MODEL_NAMESPACE',
            ],
            '{{controllerNamespace}}' => [
                'group'       => 'model',
                'description' => 'Controller namespace from config.',
                'example'     => 'App\\Http\\Controllers',
                'constant'    => 'StubTokens::CONTROLLER_NAMESPACE',
            ],

            // ── Input argument tokens ──────────────────────────────────────
            '{{getNameInput}}' => [
                'group'       => 'input',
                'description' => 'Raw value of the "name" Artisan argument (table name).',
                'example'     => 'product',
                'constant'    => 'StubTokens::GET_NAME_INPUT',
            ],
            '{{getNameInputLower}}' => [
                'group'       => 'input',
                'description' => 'Str::lower of the name argument.',
                'example'     => 'product',
                'constant'    => 'StubTokens::GET_NAME_INPUT_LOWER',
            ],
            '{{getNameInputPluralLower}}' => [
                'group'       => 'input',
                'description' => 'Lowercase plural of the name argument.',
                'example'     => 'products',
                'constant'    => 'StubTokens::GET_NAME_INPUT_PLURAL_LOWER',
            ],
            '{{getTemplate}}' => [
                'group'       => 'input',
                'description' => 'Generator template name ($templateName property, default "backend").',
                'example'     => 'backend',
                'constant'    => 'StubTokens::GET_TEMPLATE',
            ],
            '{{getTheme}}' => [
                'group'       => 'input',
                'description' => 'Theme slug from the "theme" argument.',
                'example'     => 'modern',
                'constant'    => 'StubTokens::GET_THEME',
            ],
            '{{getThemeInput}}' => [
                'group'       => 'input',
                'description' => 'Theme slug (alias for {{getTheme}}).',
                'example'     => 'modern',
                'constant'    => 'StubTokens::GET_THEME_INPUT',
            ],
            '{{getThemeInputLower}}' => [
                'group'       => 'input',
                'description' => 'Str::lower of the theme argument.',
                'example'     => 'modern',
                'constant'    => 'StubTokens::GET_THEME_INPUT_LOWER',
            ],
            '{{themelower}}' => [
                'group'       => 'input',
                'description' => 'Str::lower of the theme argument (alias).',
                'example'     => 'modern',
                'constant'    => 'StubTokens::THEME_LOWER',
            ],
            '{{getModuleInput}}' => [
                'group'       => 'input',
                'description' => 'Str::lower of the module argument.',
                'example'     => 'shop',
                'constant'    => 'StubTokens::GET_MODULE_INPUT',
            ],
            '{{getModuleInputModule}}' => [
                'group'       => 'input',
                'description' => 'Raw module argument value (PascalCase).',
                'example'     => 'Shop',
                'constant'    => 'StubTokens::GET_MODULE_INPUT_MODULE',
            ],
            '{{getModuleInputModuleNew}}' => [
                'group'       => 'input',
                'description' => 'ucfirst of the module argument.',
                'example'     => 'Shop',
                'constant'    => 'StubTokens::GET_MODULE_INPUT_MODULE_NEW',
            ],
            '{{getModuleInputTitle}}' => [
                'group'       => 'input',
                'description' => 'Str::title of the module argument.',
                'example'     => 'Shop',
                'constant'    => 'StubTokens::GET_MODULE_INPUT_TITLE',
            ],
            '{{getModuleInputClass}}' => [
                'group'       => 'input',
                'description' => 'Str::studly of the module argument.',
                'example'     => 'ShopModule',
                'constant'    => 'StubTokens::GET_MODULE_INPUT_CLASS',
            ],
            '{{getModuleInputLower}}' => [
                'group'       => 'input',
                'description' => 'Str::lower of the module argument (alias for getModuleInput).',
                'example'     => 'shop',
                'constant'    => 'StubTokens::GET_MODULE_INPUT_LOWER',
            ],
            '{{getModuleInputModuleLowerCase}}' => [
                'group'       => 'input',
                'description' => 'Str::lower of the module (used specifically in view blade paths).',
                'example'     => 'shop',
                'constant'    => 'StubTokens::GET_MODULE_INPUT_MODULE_LOWER_CASE',
            ],
            '{{layout}}' => [
                'group'       => 'input',
                'description' => 'Blade layout path from config (default: "layouts.app").',
                'example'     => 'layouts.app',
                'constant'    => 'StubTokens::LAYOUT',
            ],

            // ── Component / Model column tokens ───────────────────────────
            '{{fillable}}' => [
                'group'       => 'component',
                'description' => 'Comma-separated quoted column names for $fillable.',
                'example'     => "'name','email','status'",
                'constant'    => 'StubTokens::FILLABLE',
            ],
            '{{updatefield}}' => [
                'group'       => 'component',
                'description' => 'Public Livewire property declarations ($name, $email, ...).',
                'example'     => '$name, $email, $status',
                'constant'    => 'StubTokens::UPDATE_FIELD',
            ],
            '{{resetfields}}' => [
                'group'       => 'component',
                'description' => '$this->field = null; lines for each fillable column.',
                'example'     => "\n\t\t\$this->name = null;\n\t\t\$this->email = null;",
                'constant'    => 'StubTokens::RESET_FIELDS',
            ],
            '{{showfields}}' => [
                'group'       => 'component',
                'description' => '$this->field = $record->field; lines for show/edit.',
                'example'     => "\n\t\t\$this->name = \$record->name;",
                'constant'    => 'StubTokens::SHOW_FIELDS',
            ],
            '{{editfields}}' => [
                'group'       => 'component',
                'description' => '$this->field = $record->field; lines for edit (alias for showfields).',
                'example'     => "\n\t\t\$this->name = \$record->name;",
                'constant'    => 'StubTokens::EDIT_FIELDS',
            ],
            '{{addfields}}' => [
                'group'       => 'component',
                'description' => "\$record->field = \$this->field; lines for store/update.",
                'example'     => "\n\t\t\t'name' => \$this->name,",
                'constant'    => 'StubTokens::ADD_FIELDS',
            ],
            '{{fieldsList}}' => [
                'group'       => 'component',
                'description' => 'Comma-separated column names (for Excel headings etc.).',
                'example'     => 'name, email, status',
                'constant'    => 'StubTokens::FIELDS_LIST',
            ],
            '{{factory}}' => [
                'group'       => 'component',
                'description' => "Faker definition lines per column type inside Factory::definition().",
                'example'     => "\n\t\t\t'name' => \$this->faker->name,",
                'constant'    => 'StubTokens::FACTORY',
            ],
            '{{rules}}' => [
                'group'       => 'component',
                'description' => "Validation rule entries (e.g. 'name' => 'required',).",
                'example'     => "\n\t\t'name' => 'required',",
                'constant'    => 'StubTokens::RULES',
            ],
            '{{search}}' => [
                'group'       => 'component',
                'description' => "->orWhere('column', 'LIKE', \$keyWord) chain.",
                'example'     => "\n\t\t\t\t\t\t->orWhere('name', 'LIKE', \$keyWord)",
                'constant'    => 'StubTokens::SEARCH',
            ],
            '{{templateHeaders}}' => [
                'group'       => 'component',
                'description' => "Title-cased column names as a CSV string for Excel templates.",
                'example'     => "'Name', 'Email', 'Status'",
                'constant'    => 'StubTokens::TEMPLATE_HEADERS',
            ],
            '{{relations}}' => [
                'group'       => 'component',
                'description' => 'Eloquent relationship method stubs generated from FK analysis.',
                'example'     => 'public function user() { return $this->belongsTo(...); }',
                'constant'    => 'StubTokens::RELATIONS',
            ],
            '{{properties}}' => [
                'group'       => 'component',
                'description' => 'Additional @property docblock lines from ModelGenerator.',
                'example'     => "\n * @property string \$name",
                'constant'    => 'StubTokens::PROPERTIES',
            ],
            '{{softDeletesNamespace}}' => [
                'group'       => 'component',
                'description' => '"use Illuminate\\Database\\Eloquent\\SoftDeletes;" or empty string.',
                'example'     => 'use Illuminate\\Database\\Eloquent\\SoftDeletes;',
                'constant'    => 'StubTokens::SOFT_DELETES_NAMESPACE',
            ],
            '{{softDeletes}}' => [
                'group'       => 'component',
                'description' => '"use SoftDeletes;" or empty string, placed inside the class body.',
                'example'     => 'use SoftDeletes;',
                'constant'    => 'StubTokens::SOFT_DELETES',
            ],

            // ── View / table tokens ────────────────────────────────────────
            '{{tableHeader}}' => [
                'group'       => 'view',
                'description' => '<th>Title</th> cells for every visible column in the index table.',
                'example'     => "\t\t\t\t<th>Name</th>\n\t\t\t\t<th>Email</th>",
                'constant'    => 'StubTokens::TABLE_HEADER',
            ],
            '{{tableBody}}' => [
                'group'       => 'view',
                'description' => '<td>{{ $row->col }}</td> cells for every visible column.',
                'example'     => "\t\t\t\t<td>{{ \$row->name }}</td>",
                'constant'    => 'StubTokens::TABLE_BODY',
            ],
            '{{viewRows}}' => [
                'group'       => 'view',
                'description' => 'Rendered view-row partials per column type for the view/show page.',
                'example'     => '<x-view-row label="Name" :value="$row->name"/>',
                'constant'    => 'StubTokens::VIEW_ROWS',
            ],
            '{{form}}' => [
                'group'       => 'view',
                'description' => 'Datatype-aware form-field partials for create/edit forms.',
                'example'     => '@include("module::livewire.model.form-field", ["field"=>"name"])',
                'constant'    => 'StubTokens::FORM',
            ],
            '{{show}}' => [
                'group'       => 'view',
                'description' => 'Show-field partials for the detail/show view.',
                'example'     => '@include("module::livewire.model.show-field", ["field"=>"name"])',
                'constant'    => 'StubTokens::SHOW',
            ],
            '{{type}}' => [
                'group'       => 'view',
                'description' => 'Default HTML input type for the last column ("text" unless overridden).',
                'example'     => 'text',
                'constant'    => 'StubTokens::TYPE',
            ],

            // ── Resource class tokens ──────────────────────────────────────
            '{{resourceColumns}}' => [
                'group'       => 'resource',
                'description' => 'Column builder fluent calls for Resource::table() (TextColumn, DateColumn, …).',
                'example'     => "TextColumn::make('name')->label('Name')->searchable()->sortable(),",
                'constant'    => 'StubTokens::RESOURCE_COLUMNS',
            ],
            '{{resourceFormFields}}' => [
                'group'       => 'resource',
                'description' => 'Field builder fluent calls for Resource::form() (TextInput, Toggle, …).',
                'example'     => "TextInput::make('name')->label('Name')->required(),",
                'constant'    => 'StubTokens::RESOURCE_FORM_FIELDS',
            ],
            '{{resourceFilters}}' => [
                'group'       => 'resource',
                'description' => 'Filter builder fluent calls for Resource::table() filters array.',
                'example'     => "SelectFilter::make('status')->label('Status'),",
                'constant'    => 'StubTokens::RESOURCE_FILTERS',
            ],
            '{{navigationIcon}}' => [
                'group'       => 'resource',
                'description' => 'Bootstrap Icons class for the sidebar navigation item (default: "bi-table").',
                'example'     => 'bi-table',
                'constant'    => 'StubTokens::NAVIGATION_ICON',
            ],

            // ── Per-field partial tokens ───────────────────────────────────
            '{{title}}' => [
                'group'       => 'field',
                'description' => 'Human-readable column title (Str::title of snake column name).',
                'example'     => 'First Name',
                'constant'    => 'StubTokens::TITLE',
            ],
            '{{column}}' => [
                'group'       => 'field',
                'description' => 'Raw snake_case column name as it appears in the DB table.',
                'example'     => 'first_name',
                'constant'    => 'StubTokens::COLUMN',
            ],
            '{{datatype}}' => [
                'group'       => 'field',
                'description' => 'HTML input type string returned by TypeMapper::htmlInputType() (text, email, number, date, …).',
                'example'     => 'email',
                'constant'    => 'StubTokens::DATATYPE',
            ],
        ];
    }

    // -----------------------------------------------------------------------
    // Stub ↔ Token cross-reference map
    // -----------------------------------------------------------------------

    /**
     * Complete mapping of stub file paths (relative to src/stubs/) to the
     * list of tokens they consume.
     *
     * Generated by scanning every .stub file in the package.
     *
     * @return array<string, string[]>
     */
    public static function stubMap(): array
    {
        return [
            'Chart.stub' => [
                '{{getModuleInputModule}}',
                '{{modelName}}',
                '{{modelPluralTitle}}',
            ],
            'Email.stub' => [
                '{{getModuleInputModule}}',
                '{{getModuleInputModuleLowerCase}}',
                '{{modelName}}',
                '{{modelNameLowerCase}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelPluralTitle}}',
            ],
            'Export.stub' => [
                '{{getModuleInputModule}}',
                '{{modelName}}',
                '{{modelPluralName}}',
                '{{modelPluralTitle}}',
            ],
            'Factory.stub' => [
                '{{factory}}',
                '{{getModuleInputModule}}',
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}',
            ],
            'Fullcalendar.stub' => [
                '{{getModuleInput}}',
                '{{getModuleInputModule}}',
                '{{getNameInput}}',
                '{{modelName}}',
            ],
            'Import.stub' => [
                '{{getModuleInputModule}}',
                '{{modelName}}',
                '{{modelPluralName}}',
            ],
            'Livewire.stub' => [
                '{{addfields}}',
                '{{editfields}}',
                '{{getModuleInput}}',
                '{{getModuleInputModule}}',
                '{{getNameInput}}',
                '{{modelName}}',
                '{{modelNameLowerCase}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}',
                '{{modelPluralName}}',
                '{{modelPluralTitle}}',
                '{{modelTitle}}',
                '{{resetfields}}',
                '{{rules}}',
                '{{showfields}}',
                '{{updatefield}}',
            ],
            'LivewireVolt.stub' => [
                '{{form}}',
                '{{getModuleInput}}',
                '{{getModuleInputModule}}',
                '{{modelName}}',
                '{{modelNameLowerCase}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelPluralName}}',
                '{{modelPluralTitle}}',
                '{{resetfields}}',
                '{{rules}}',
                '{{search}}',
                '{{showfields}}',
                '{{tableBody}}',
                '{{tableHeader}}',
                '{{updatefield}}',
            ],
            'Livewirethemed.stub' => [
                '{{addfields}}',
                '{{editfields}}',
                '{{getModuleInput}}',
                '{{getModuleInputModule}}',
                '{{getNameInput}}',
                '{{modelName}}',
                '{{modelNameLowerCase}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}',
                '{{modelPluralName}}',
                '{{modelPluralTitle}}',
                '{{modelTitle}}',
                '{{resetfields}}',
                '{{rules}}',
                '{{showfields}}',
                '{{updatefield}}',
            ],
            'Model.stub' => [
                '{{fillable}}',
                '{{getModuleInputModule}}',
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{relations}}',
            ],
            'Notification.stub' => [
                '{{getModuleInput}}',
                '{{getModuleInputModule}}',
                '{{getNameInput}}',
                '{{modelName}}',
                '{{modelNameLowerCase}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelPluralTitle}}',
            ],
            'PdfExport.stub' => [
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelPluralTitle}}',
            ],
            'Print.stub' => [
                '{{getModuleInputModule}}',
                '{{getModuleInputModuleLowerCase}}',
                '{{modelName}}',
                '{{modelNameLowerCase}}',
            ],
            'Resource.stub' => [
                '{{getModuleInputModule}}',
                '{{getModuleInputTitle}}',
                '{{modelName}}',
                '{{modelNamePluralTitle}}',
                '{{resourceColumns}}',
                '{{resourceFilters}}',
                '{{resourceFormFields}}',
            ],
            'Upload.stub' => [
                '{{getModuleInputModule}}',
                '{{modelPluralName}}',
            ],
            // ── modals ────────────────────────────────────────────────────
            'modals/Create.stub' => [
                '{{addfields}}',
                '{{getModuleInput}}',
                '{{getModuleInputModule}}',
                '{{getModuleInputModuleNew}}',
                '{{getNameInput}}',
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelPluralName}}',
                '{{rules}}',
                '{{updatefield}}',
            ],
            'modals/Delete.stub' => [
                '{{editfields}}',
                '{{getModuleInput}}',
                '{{getModuleInputModule}}',
                '{{getModuleInputModuleNew}}',
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelPluralName}}',
                '{{updatefield}}',
            ],
            'modals/Edit.stub' => [
                '{{addfields}}',
                '{{editfields}}',
                '{{getModuleInput}}',
                '{{getModuleInputModule}}',
                '{{getModuleInputModuleNew}}',
                '{{getNameInput}}',
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelPluralName}}',
                '{{rules}}',
                '{{updatefield}}',
            ],
            'modals/Show.stub' => [
                '{{getModuleInput}}',
                '{{getModuleInputModule}}',
                '{{getModuleInputModuleNew}}',
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelPluralName}}',
                '{{showfields}}',
                '{{updatefield}}',
            ],
            // ── modalsthemed ──────────────────────────────────────────────
            'modalsthemed/Create.stub' => [
                '{{addfields}}',
                '{{getModuleInput}}',
                '{{getModuleInputModuleNew}}',
                '{{getNameInput}}',
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelPluralName}}',
                '{{rules}}',
                '{{updatefield}}',
            ],
            'modalsthemed/Delete.stub' => [
                '{{editfields}}',
                '{{getModuleInput}}',
                '{{getModuleInputModuleNew}}',
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelPluralName}}',
                '{{updatefield}}',
            ],
            'modalsthemed/Edit.stub' => [
                '{{addfields}}',
                '{{editfields}}',
                '{{getModuleInput}}',
                '{{getModuleInputModuleNew}}',
                '{{getNameInput}}',
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelPluralName}}',
                '{{rules}}',
                '{{updatefield}}',
            ],
            'modalsthemed/Show.stub' => [
                '{{getModuleInput}}',
                '{{getModuleInputModuleNew}}',
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelPluralName}}',
                '{{showfields}}',
                '{{updatefield}}',
            ],
            // ── views (base) ──────────────────────────────────────────────
            'views/chart.stub' => [
                '{{getModuleInput}}',
                '{{getNameInput}}',
                '{{modelNameLowerCase}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelPluralTitle}}',
            ],
            'views/chart_new.stub' => [
                '{{getModuleInput}}',
                '{{getNameInput}}',
                '{{modelNameLowerCase}}',
                '{{modelPluralTitle}}',
            ],
            'views/create.stub' => [
                '{{form}}',
                '{{getModuleInput}}',
                '{{modelNameLowerCase}}',
                '{{modelNamePluralLowerCase}}',
            ],
            'views/delete.stub' => [
                '{{modelNameLowerCase}}',
                '{{modelTitle}}',
            ],
            'views/email.stub' => [
                '{{getModuleInput}}',
                '{{getModuleInputModuleLowerCase}}',
                '{{getNameInput}}',
                '{{modelName}}',
                '{{modelNameLowerCase}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelPluralTitle}}',
            ],
            'views/form-field.stub' => [
                '{{column}}',
                '{{datatype}}',
                '{{title}}',
            ],
            'views/fullcalender.stub' => [
                '{{getModuleInput}}',
                '{{getNameInput}}',
                '{{modelPluralTitle}}',
                '{{modelTitle}}',
            ],
            'views/import.stub' => [
                '{{modelNamePluralLowerCase}}',
            ],
            'views/index.stub' => [
                '{{getModuleInput}}',
                '{{getTemplate}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelPluralTitle}}',
            ],
            'views/notification.stub' => [
                '{{getModuleInput}}',
                '{{getNameInput}}',
                '{{modelName}}',
                '{{modelNameLowerCase}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelPluralTitle}}',
            ],
            'views/pdf-export.stub' => [
                '{{getModuleInputModule}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelPluralTitle}}',
                '{{tableBody}}',
                '{{tableHeader}}',
            ],
            'views/print.stub' => [
                '{{modelName}}',
                '{{showfields}}',
            ],
            'views/show.stub' => [
                '{{modelName}}',
                '{{modelNameLowerCase}}',
                '{{modelTitle}}',
                '{{show}}',
            ],
            'views/show-field.stub' => [
                '{{column}}',
                '{{modelNameLowerCase}}',
                '{{title}}',
            ],
            'views/update.stub' => [
                '{{form}}',
                '{{getModuleInput}}',
                '{{modelNameLowerCase}}',
                '{{modelNamePluralLowerCase}}',
            ],
            'views/view.stub' => [
                '{{getModuleInput}}',
                '{{modelName}}',
                '{{modelNameLowerCase}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelPluralTitle}}',
                '{{tableBody}}',
                '{{tableHeader}}',
            ],
            'views/viewold.stub' => [
                '{{getModuleInput}}',
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelPluralTitle}}',
                '{{modelTitle}}',
                '{{tableBody}}',
                '{{tableHeader}}',
            ],
            'views/view-row.stub' => [
                '{{modelName}}',
                '{{showfields}}',
            ],
            'views/view-row-field.stub' => [
                '{{column}}',
                '{{title}}',
            ],
            'views/datatype/checkbox.stub' => [
                '{{column}}',
                '{{title}}',
            ],
            'views/datatype/date.stub' => [
                '{{column}}',
                '{{title}}',
            ],
            'views/datatype/number.stub' => [
                '{{column}}',
                '{{datatype}}',
                '{{title}}',
            ],
            'views/datatype/select.stub' => [
                '{{column}}',
                '{{title}}',
            ],
            'views/datatype/text.stub' => [
                '{{column}}',
                '{{datatype}}',
                '{{title}}',
            ],
            'views/datatype/textarea.stub' => [
                '{{column}}',
                '{{title}}',
            ],
            'views/datatype/time.stub' => [
                '{{column}}',
                '{{title}}',
            ],
            // ── viewsdefault ──────────────────────────────────────────────
            'viewsdefault/create.stub' => [
                '{{form}}',
                '{{modelTitle}}',
            ],
            'viewsdefault/delete.stub' => [
                '{{modelTitle}}',
            ],
            'viewsdefault/form-field.stub' => [
                '{{column}}',
                '{{title}}',
                '{{type}}',
            ],
            'viewsdefault/import.stub' => [
                '{{modelNamePluralLowerCase}}',
            ],
            'viewsdefault/index.stub' => [
                '{{getModuleInput}}',
                '{{getTemplate}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelTitle}}',
            ],
            'viewsdefault/mobile_index.stub' => [
                '{{getModuleInputLower}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelTitle}}',
            ],
            'viewsdefault/pdf-export.stub' => [
                '{{getModuleInputModule}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelPluralTitle}}',
                '{{tableBody}}',
                '{{tableHeader}}',
            ],
            'viewsdefault/print.stub' => [
                '{{modelName}}',
                '{{showfields}}',
            ],
            'viewsdefault/show.stub' => [
                '{{modelTitle}}',
                '{{show}}',
            ],
            'viewsdefault/show-field.stub' => [
                '{{column}}',
                '{{title}}',
            ],
            'viewsdefault/update.stub' => [
                '{{form}}',
                '{{modelTitle}}',
            ],
            'viewsdefault/view.stub' => [
                '{{getModuleInput}}',
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelTitle}}',
                '{{tableBody}}',
                '{{tableHeader}}',
            ],
            // ── themes/default ────────────────────────────────────────────
            'themes/default/create.stub' => [
                '{{form}}',
                '{{modelTitle}}',
            ],
            'themes/default/delete.stub' => [
                '{{modelTitle}}',
            ],
            'themes/default/form-field.stub' => [
                '{{column}}',
                '{{title}}',
                '{{type}}',
            ],
            'themes/default/index.stub' => [
                '{{getModuleInput}}',
                '{{getTemplate}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelTitle}}',
            ],
            'themes/default/show.stub' => [
                '{{modelTitle}}',
                '{{show}}',
            ],
            'themes/default/show-field.stub' => [
                '{{column}}',
                '{{title}}',
            ],
            'themes/default/update.stub' => [
                '{{form}}',
                '{{modelTitle}}',
            ],
            'themes/default/view.stub' => [
                '{{getModuleInput}}',
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelTitle}}',
                '{{tableBody}}',
                '{{tableHeader}}',
            ],
            'themes/default/viewold.stub' => [
                '{{getModuleInput}}',
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelTitle}}',
                '{{tableBody}}',
                '{{tableHeader}}',
            ],
            'themes/default/views/create.stub' => [
                '{{form}}',
                '{{modelTitle}}',
            ],
            'themes/default/views/delete.stub' => [
                '{{modelTitle}}',
            ],
            'themes/default/views/form-field.stub' => [
                '{{column}}',
                '{{title}}',
                '{{type}}',
            ],
            'themes/default/views/import.stub' => [
                '{{modelNamePluralLowerCase}}',
            ],
            'themes/default/views/index.stub' => [
                '{{getModuleInput}}',
                '{{getTemplate}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelTitle}}',
            ],
            'themes/default/views/mobile_index.stub' => [
                '{{getModuleInputLower}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelTitle}}',
            ],
            'themes/default/views/pdf-export.stub' => [
                '{{getModuleInputModule}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelPluralTitle}}',
                '{{tableBody}}',
                '{{tableHeader}}',
            ],
            'themes/default/views/print.stub' => [
                '{{modelName}}',
                '{{showfields}}',
            ],
            'themes/default/views/show.stub' => [
                '{{modelTitle}}',
                '{{show}}',
            ],
            'themes/default/views/show-field.stub' => [
                '{{column}}',
                '{{title}}',
            ],
            'themes/default/views/update.stub' => [
                '{{form}}',
                '{{modelTitle}}',
            ],
            'themes/default/views/view.stub' => [
                '{{getModuleInput}}',
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelTitle}}',
                '{{tableBody}}',
                '{{tableHeader}}',
            ],
            // ── themes/modern ─────────────────────────────────────────────
            'themes/modern/chart.stub' => [
                '{{getModuleInput}}',
                '{{getNameInput}}',
                '{{modelNameLowerCase}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelPluralTitle}}',
            ],
            'themes/modern/chart_new.stub' => [
                '{{getModuleInput}}',
                '{{getNameInput}}',
                '{{modelNameLowerCase}}',
                '{{modelPluralTitle}}',
            ],
            'themes/modern/create.stub' => [
                '{{form}}',
                '{{getModuleInput}}',
                '{{modelNameLowerCase}}',
                '{{modelNamePluralLowerCase}}',
            ],
            'themes/modern/delete.stub' => [
                '{{modelNameLowerCase}}',
                '{{modelTitle}}',
            ],
            'themes/modern/email.stub' => [
                '{{getModuleInput}}',
                '{{getModuleInputModuleLowerCase}}',
                '{{getNameInput}}',
                '{{modelName}}',
                '{{modelNameLowerCase}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelPluralTitle}}',
            ],
            'themes/modern/form-field.stub' => [
                '{{column}}',
                '{{datatype}}',
                '{{title}}',
            ],
            'themes/modern/fullcalender.stub' => [
                '{{getModuleInput}}',
                '{{getNameInput}}',
                '{{modelPluralTitle}}',
                '{{modelTitle}}',
            ],
            'themes/modern/import.stub' => [
                '{{modelNamePluralLowerCase}}',
            ],
            'themes/modern/index.stub' => [
                '{{getModuleInput}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelPluralTitle}}',
            ],
            'themes/modern/mobile_index.stub' => [
                '{{getModuleInput}}',
                '{{modelNamePluralLowerCase}}',
            ],
            'themes/modern/notification.stub' => [
                '{{getModuleInput}}',
                '{{getNameInput}}',
                '{{modelName}}',
                '{{modelNameLowerCase}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelPluralTitle}}',
            ],
            'themes/modern/pdf-export.stub' => [
                '{{getModuleInputModule}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelPluralTitle}}',
                '{{tableBody}}',
                '{{tableHeader}}',
            ],
            'themes/modern/print.stub' => [
                '{{modelName}}',
                '{{showfields}}',
            ],
            'themes/modern/show.stub' => [
                '{{modelName}}',
                '{{modelNameLowerCase}}',
                '{{modelTitle}}',
                '{{show}}',
            ],
            'themes/modern/show-field.stub' => [
                '{{column}}',
                '{{modelNameLowerCase}}',
                '{{title}}',
            ],
            'themes/modern/update.stub' => [
                '{{form}}',
                '{{getModuleInput}}',
                '{{modelNameLowerCase}}',
                '{{modelNamePluralLowerCase}}',
            ],
            'themes/modern/view.stub' => [
                '{{getModuleInput}}',
                '{{modelName}}',
                '{{modelNameLowerCase}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelPluralTitle}}',
                '{{tableBody}}',
                '{{tableHeader}}',
                '{{themelower}}',
            ],
            'themes/modern/viewold.stub' => [
                '{{getModuleInput}}',
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelPluralTitle}}',
                '{{modelTitle}}',
                '{{tableBody}}',
                '{{tableHeader}}',
            ],
            'themes/modern/view-row.stub' => [
                '{{modelName}}',
                '{{showfields}}',
            ],
            'themes/modern/view-row-field.stub' => [
                '{{column}}',
                '{{title}}',
            ],
            'themes/modern/datatype/checkbox.stub' => [
                '{{column}}',
                '{{title}}',
            ],
            'themes/modern/datatype/date.stub' => [
                '{{column}}',
                '{{title}}',
            ],
            'themes/modern/datatype/number.stub' => [
                '{{column}}',
                '{{datatype}}',
                '{{title}}',
            ],
            'themes/modern/datatype/select.stub' => [
                '{{column}}',
                '{{title}}',
            ],
            'themes/modern/datatype/text.stub' => [
                '{{column}}',
                '{{datatype}}',
                '{{title}}',
            ],
            'themes/modern/datatype/textarea.stub' => [
                '{{column}}',
                '{{title}}',
            ],
            'themes/modern/datatype/time.stub' => [
                '{{column}}',
                '{{title}}',
            ],
            'themes/modern/views/chart.stub' => [
                '{{getModuleInput}}',
                '{{getNameInput}}',
                '{{modelNameLowerCase}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelPluralTitle}}',
            ],
            'themes/modern/views/chart_new.stub' => [
                '{{getModuleInput}}',
                '{{getNameInput}}',
                '{{modelNameLowerCase}}',
                '{{modelPluralTitle}}',
            ],
            'themes/modern/views/create.stub' => [
                '{{form}}',
                '{{modelNameLowerCase}}',
            ],
            'themes/modern/views/delete.stub' => [
                '{{modelNameLowerCase}}',
                '{{modelTitle}}',
            ],
            'themes/modern/views/email.stub' => [
                '{{getModuleInput}}',
                '{{getModuleInputModuleLowerCase}}',
                '{{getNameInput}}',
                '{{modelName}}',
                '{{modelNameLowerCase}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelPluralTitle}}',
            ],
            'themes/modern/views/form-field.stub' => [
                '{{column}}',
                '{{datatype}}',
                '{{title}}',
            ],
            'themes/modern/views/fullcalender.stub' => [
                '{{getModuleInput}}',
                '{{getNameInput}}',
                '{{modelPluralTitle}}',
                '{{modelTitle}}',
            ],
            'themes/modern/views/import.stub' => [
                '{{modelNamePluralLowerCase}}',
            ],
            'themes/modern/views/index.stub' => [
                '{{getModuleInputLower}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelPluralTitle}}',
            ],
            'themes/modern/views/mobile_index.stub' => [
                '{{getModuleInputLower}}',
                '{{modelNamePluralLowerCase}}',
            ],
            'themes/modern/views/notification.stub' => [
                '{{getModuleInput}}',
                '{{getNameInput}}',
                '{{modelName}}',
                '{{modelNameLowerCase}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelPluralTitle}}',
            ],
            'themes/modern/views/pdf-export.stub' => [
                '{{getModuleInputModule}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelPluralTitle}}',
                '{{tableBody}}',
                '{{tableHeader}}',
            ],
            'themes/modern/views/print.stub' => [
                '{{modelName}}',
                '{{showfields}}',
            ],
            'themes/modern/views/show.stub' => [
                '{{modelName}}',
                '{{modelNameLowerCase}}',
                '{{modelTitle}}',
                '{{show}}',
            ],
            'themes/modern/views/show-field.stub' => [
                '{{column}}',
                '{{modelNameLowerCase}}',
                '{{title}}',
            ],
            'themes/modern/views/update.stub' => [
                '{{form}}',
                '{{modelNameLowerCase}}',
            ],
            'themes/modern/views/view.stub' => [
                '{{getModuleInputLower}}',
                '{{getThemeInputLower}}',
                '{{modelName}}',
                '{{modelNameLowerCase}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelPluralTitle}}',
                '{{tableBody}}',
                '{{tableHeader}}',
            ],
            'themes/modern/views/viewold.stub' => [
                '{{getModuleInput}}',
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelPluralTitle}}',
                '{{modelTitle}}',
                '{{tableBody}}',
                '{{tableHeader}}',
            ],
            'themes/modern/views/view-row.stub' => [
                '{{modelName}}',
                '{{showfields}}',
            ],
            'themes/modern/views/view-row-field.stub' => [
                '{{column}}',
                '{{title}}',
            ],
            'themes/modern/views/datatype/checkbox.stub' => [
                '{{column}}',
                '{{title}}',
            ],
            'themes/modern/views/datatype/date.stub' => [
                '{{column}}',
                '{{title}}',
            ],
            'themes/modern/views/datatype/number.stub' => [
                '{{column}}',
                '{{datatype}}',
                '{{title}}',
            ],
            'themes/modern/views/datatype/select.stub' => [
                '{{column}}',
                '{{title}}',
            ],
            'themes/modern/views/datatype/text.stub' => [
                '{{column}}',
                '{{datatype}}',
                '{{title}}',
            ],
            'themes/modern/views/datatype/textarea.stub' => [
                '{{column}}',
                '{{title}}',
            ],
            'themes/modern/views/datatype/time.stub' => [
                '{{column}}',
                '{{title}}',
            ],
        ];
    }

    // -----------------------------------------------------------------------
    // Query helpers
    // -----------------------------------------------------------------------

    /**
     * Return all stub file paths (relative to src/stubs/) that use a given token.
     *
     * @param  string  $token  e.g. '{{modelName}}' or 'modelName'
     * @return string[]
     */
    public static function stubsUsingToken(string $token): array
    {
        // Normalise: wrap bare name in {{ }}
        if (!str_starts_with($token, '{{')) {
            $token = '{{' . trim($token, '{}') . '}}';
        }

        $result = [];
        foreach (self::stubMap() as $file => $tokens) {
            if (in_array($token, $tokens, true)) {
                $result[] = $file;
            }
        }
        return $result;
    }

    /**
     * Return all tokens used by a specific stub file.
     *
     * @param  string  $stubPath  Relative path, e.g. 'Factory.stub' or 'views/index.stub'
     * @return string[]
     */
    public static function tokensInStub(string $stubPath): array
    {
        // Normalise: forward slashes, strip leading slash
        $stubPath = ltrim(str_replace('\\', '/', $stubPath), '/');
        return self::stubMap()[$stubPath] ?? [];
    }

    /**
     * Return metadata for a single token, or null if not registered.
     *
     * @return array{group: string, description: string, example: string, constant: string}|null
     */
    public static function get(string $token): ?array
    {
        if (!str_starts_with($token, '{{')) {
            $token = '{{' . trim($token, '{}') . '}}';
        }
        return self::all()[$token] ?? null;
    }

    /**
     * Return all tokens belonging to a specific group.
     *
     * @param  string  $group  One of: model | input | component | view | resource | field
     * @return string[]  Token keys belonging to that group.
     */
    public static function tokensInGroup(string $group): array
    {
        return array_keys(array_filter(
            self::all(),
            fn ($meta) => $meta['group'] === $group
        ));
    }

    /**
     * Scan a stub string and return any tokens that are NOT in this reference.
     *
     * Useful for CI validation: run this against every .stub file to catch
     * typos or new tokens that were added without being registered here.
     *
     * @return string[]  Unknown/unregistered tokens.
     */
    public static function validate(string $stubContent): array
    {
        preg_match_all('/\{\{[a-zA-Z0-9_]+\}\}/', $stubContent, $matches);
        $found = array_unique($matches[0] ?? []);
        $known = array_keys(self::all());
        return array_values(array_filter($found, fn ($t) => !in_array($t, $known, true)));
    }

    /**
     * Cross-reference: for every token in StubTokens, confirm it appears in
     * at least one stub file.  Returns tokens that are defined but never used.
     *
     * @return string[]  Orphaned token keys.
     */
    public static function orphanedTokens(): array
    {
        $allUsed = array_unique(array_merge(...array_values(self::stubMap())));
        $registered = array_keys(self::all());
        return array_values(array_diff($registered, $allUsed));
    }
}
