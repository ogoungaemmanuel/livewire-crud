<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Support;

use Illuminate\Support\Str;

/**
 * StubTokens — canonical registry of all stub replacement tokens.
 *
 * Every {{token}} used across stubs and view partials is defined here with:
 *  - its token string (key)
 *  - the runtime value resolver (closures receive the generator context)
 *  - a short description
 *
 * ┌──────────────────────────────────────────────────────────────────────────┐
 * │  TOKEN REFERENCE                                                         │
 * ├───────────────────────────┬──────────────────────────────────────────────┤
 * │  Token                    │  Resolved from                               │
 * ├───────────────────────────┼──────────────────────────────────────────────┤
 * │  {{modelName}}            │  Studly-case model name (e.g. "Product")     │
 * │  {{modelNameLowerCase}}   │  camelCase model name (e.g. "product")       │
 * │  {{modelNameSingularLowerCase}} │  Str::lower($name) (e.g. "product")   │
 * │  {{modelPluralName}}      │  Plural studly (e.g. "Products")             │
 * │  {{modelTitle}}           │  Title-case from snake (e.g. "My Model")     │
 * │  {{modelPluralTitle}}     │  Title-case plural (e.g. "My Models")        │
 * │  {{modelNamePluralLowerCase}} │  camelCase plural (e.g. "products")      │
 * │  {{modelNamePluralUpperCase}} │  ucfirst plural (e.g. "Products")        │
 * │  {{modelRoute}}           │  kebab-case plural URL (e.g. "my-models")    │
 * │  {{modelView}}            │  kebab-case singular view name               │
 * │  {{modelNamespace}}       │  Model namespace from config                 │
 * │  {{controllerNamespace}}  │  Controller namespace from config            │
 * ├───────────────────────────┼──────────────────────────────────────────────┤
 * │  {{getNameInput}}         │  Raw table/input name argument               │
 * │  {{getNameInputLower}}    │  Lowercase table input name                  │
 * │  {{getNameInputPluralLower}} │  Lowercase plural table input name        │
 * │  {{getTemplate}}          │  Template name from $templateName property   │
 * │  {{getTheme}}             │  Theme slug (e.g. "modern", "none")          │
 * │  {{getThemeInput}}        │  Theme slug (alias)                          │
 * │  {{getThemeInputLower}}   │  Lowercase theme slug                        │
 * │  {{themelower}}           │  Lowercase theme slug (alias)                │
 * ├───────────────────────────┼──────────────────────────────────────────────┤
 * │  {{getModuleInput}}       │  Lowercase module name (e.g. "shop")         │
 * │  {{getModuleInputModule}} │  Raw module name (e.g. "Shop")               │
 * │  {{getModuleInputModuleNew}} │  ucfirst module name                      │
 * │  {{getModuleInputTitle}}  │  title-case module name                      │
 * │  {{getModuleInputClass}}  │  Studly module name                          │
 * │  {{getModuleInputLower}}  │  Lowercase module name (alias)               │
 * │  {{getModuleInputModuleLowerCase}} │ Lowercase module name (for views)      │
 * ├───────────────────────────┼──────────────────────────────────────────────┤
 * │  {{layout}}               │  Layout blade path from config               │
 * ├───────────────────────────┼──────────────────────────────────────────────┤
 * │  COMPONENT / MODEL TOKENS  (generated from DB columns)                   │
 * ├───────────────────────────┼──────────────────────────────────────────────┤
 * │  {{fillable}}             │  $fillable array entries for Model           │
 * │  {{updatefield}}          │  Public property declarations in Livewire    │
 * │  {{resetfields}}          │  $this->field = '' reset lines               │
 * │  {{showfields}}           │  $this->field = $record->field lines         │
 * │  {{editfields}}           │  $this->field = $record->field (edit)        │
 * │  {{addfields}}            │  $record->field = $this->field lines         │
 * │  {{fieldsList}}           │  Comma-separated field names for Excel       │
 * │  {{factory}}              │  Factory fake() lines per column type        │
 * │  {{rules}}                │  Livewire validation rule array entries       │
 * │  {{search}}               │  OrWhere chain for keyword search             │
 * │  {{templateHeaders}}      │  Excel template header columns               │
 * │  {{relations}}            │  Eloquent relationship methods               │
 * │  {{properties}}           │  Additional Livewire public property lines    │
 * │  {{softDeletesNamespace}} │  "use Illuminate\Database\Eloquent\SoftDeletes;" │
 * │  {{softDeletes}}          │  "use SoftDeletes;" (or empty)               │
 * ├───────────────────────────┼──────────────────────────────────────────────┤
 * │  VIEW / TABLE TOKENS  (generated from DB columns per CRUD view)          │
 * ├───────────────────────────┼──────────────────────────────────────────────┤
 * │  {{tableHeader}}          │  <th> cells for each visible column          │
 * │  {{tableBody}}            │  <td> cells for each visible column          │
 * │  {{viewRows}}             │  View-row partials per column type            │
 * │  {{form}}                 │  Form field partials (datatype-aware)         │
 * │  {{show}}                 │  Show-field partials for detail view          │
 * │  {{type}}                 │  Default input type ("text" unless overridden)│
 * ├───────────────────────────┼──────────────────────────────────────────────┤
 * │  RESOURCE CLASS TOKENS                                                    │
 * ├───────────────────────────┼──────────────────────────────────────────────┤
 * │  {{resourceColumns}}      │  Column builder lines for Resource::table()  │
 * │  {{resourceFormFields}}   │  Field builder lines for Resource::form()    │
 * │  {{resourceFilters}}      │  Filter builder lines for Resource::table()  │
 * │  {{navigationIcon}}       │  Bootstrap icon class (default "bi-table")   │
 * │  {{modelNamePluralTitle}} │  Title-case plural model name                │
 * ├───────────────────────────┼──────────────────────────────────────────────┤
 * │  FIELD-LEVEL TOKENS  (used inside view/form-field partials)              │
 * ├───────────────────────────┼──────────────────────────────────────────────┤
 * │  {{title}}                │  Human-readable column title                 │
 * │  {{column}}               │  Raw column name (snake_case)                │
 * │  {{datatype}}             │  HTML input type (text, email, number …)     │
 * └───────────────────────────┴──────────────────────────────────────────────┘
 *
 * Usage:
 *   // Retrieve the full replacement map (for string templating):
 *   $map = StubTokens::buildReplacements($generatorCommand);
 *
 *   // Get just the token keys (useful for validation / IDE helpers):
 *   $keys = StubTokens::keys();
 *
 *   // Check if a token is registered:
 *   StubTokens::has('{{tableHeader}}'); // → true / false
 */
final class StubTokens
{
    // -----------------------------------------------------------------------
    // Groups: kept as constants so stubs and IDE tooling can reference them
    // -----------------------------------------------------------------------

    /**
     * Tokens that describe the model / class name in various casings.
     * Used in: Model.stub, Livewire.stub, Livewirethemed.stub, Resource.stub,
     *          all view stubs, route/nav hooks.
     */
    public const GROUP_MODEL = 'model';

    /**
     * Tokens that describe the current input arguments (table name, module, theme).
     * Used in: all stubs.
     */
    public const GROUP_INPUT = 'input';

    /**
     * Tokens generated from DB column analysis for the Livewire component / Model.
     * Used in: Livewire.stub, Livewirethemed.stub, Model.stub, Factory.stub.
     */
    public const GROUP_COMPONENT = 'component';

    /**
     * Tokens generated from DB column analysis for Blade view files.
     * Used in: views/index.stub, views/create.stub, views/update.stub,
     *          views/show.stub, views/delete.stub.
     */
    public const GROUP_VIEW = 'view';

    /**
     * Tokens generated for the CrudResource class stub.
     * Used in: Resource.stub.
     */
    public const GROUP_RESOURCE = 'resource';

    /**
     * Per-field-partial tokens (injected when rendering a single field row).
     * Used in: views/form-field.stub, views/show-field.stub,
     *          views/view-row.stub, views/datatype/*.stub.
     */
    public const GROUP_FIELD = 'field';

    // -----------------------------------------------------------------------
    // Token key constants
    // All double-brace tokens used anywhere in the stub pipeline.
    // -----------------------------------------------------------------------

    // ── Model / class name tokens ──────────────────────────────────────────
    public const MODEL_NAME                          = '{{modelName}}';
    public const MODEL_NAME_LOWER                    = '{{modelNameLowerCase}}';
    public const MODEL_NAME_SINGULAR_LOWER           = '{{modelNameSingularLowerCase}}';
    public const MODEL_PLURAL_NAME                   = '{{modelPluralName}}';
    public const MODEL_TITLE                         = '{{modelTitle}}';
    public const MODEL_PLURAL_TITLE                  = '{{modelPluralTitle}}';
    public const MODEL_NAME_PLURAL_LOWER             = '{{modelNamePluralLowerCase}}';
    public const MODEL_NAME_PLURAL_UPPER             = '{{modelNamePluralUpperCase}}';
    public const MODEL_NAME_PLURAL_TITLE             = '{{modelNamePluralTitle}}';
    public const MODEL_ROUTE                         = '{{modelRoute}}';
    public const MODEL_VIEW                          = '{{modelView}}';
    public const MODEL_NAMESPACE                     = '{{modelNamespace}}';
    public const CONTROLLER_NAMESPACE                = '{{controllerNamespace}}';

    // ── Input argument tokens ──────────────────────────────────────────────
    public const GET_NAME_INPUT                      = '{{getNameInput}}';
    public const GET_NAME_INPUT_LOWER                = '{{getNameInputLower}}';
    public const GET_NAME_INPUT_PLURAL_LOWER         = '{{getNameInputPluralLower}}';
    public const GET_TEMPLATE                        = '{{getTemplate}}';
    public const GET_THEME                           = '{{getTheme}}';
    public const GET_THEME_INPUT                     = '{{getThemeInput}}';
    public const GET_THEME_INPUT_LOWER               = '{{getThemeInputLower}}';
    public const THEME_LOWER                         = '{{themelower}}';
    public const GET_MODULE_INPUT                    = '{{getModuleInput}}';
    public const GET_MODULE_INPUT_MODULE             = '{{getModuleInputModule}}';
    public const GET_MODULE_INPUT_MODULE_NEW         = '{{getModuleInputModuleNew}}';
    public const GET_MODULE_INPUT_TITLE              = '{{getModuleInputTitle}}';
    public const GET_MODULE_INPUT_CLASS              = '{{getModuleInputClass}}';
    public const GET_MODULE_INPUT_LOWER              = '{{getModuleInputLower}}';
    public const GET_MODULE_INPUT_MODULE_LOWER_CASE  = '{{getModuleInputModuleLowerCase}}';
    public const LAYOUT                              = '{{layout}}';

    // ── Component / Model column tokens ───────────────────────────────────
    public const FILLABLE                            = '{{fillable}}';
    public const UPDATE_FIELD                        = '{{updatefield}}';
    public const RESET_FIELDS                        = '{{resetfields}}';
    public const SHOW_FIELDS                         = '{{showfields}}';
    public const EDIT_FIELDS                         = '{{editfields}}';
    public const ADD_FIELDS                          = '{{addfields}}';
    public const FIELDS_LIST                         = '{{fieldsList}}';
    public const FACTORY                             = '{{factory}}';
    public const RULES                               = '{{rules}}';
    public const SEARCH                              = '{{search}}';
    public const TEMPLATE_HEADERS                    = '{{templateHeaders}}';
    public const RELATIONS                           = '{{relations}}';
    public const PROPERTIES                          = '{{properties}}';
    public const SOFT_DELETES_NAMESPACE              = '{{softDeletesNamespace}}';
    public const SOFT_DELETES                        = '{{softDeletes}}';

    // ── View / table tokens ────────────────────────────────────────────────
    public const TABLE_HEADER                        = '{{tableHeader}}';
    public const TABLE_BODY                          = '{{tableBody}}';
    public const VIEW_ROWS                           = '{{viewRows}}';
    public const FORM                                = '{{form}}';
    public const SHOW                                = '{{show}}';
    public const TYPE                                = '{{type}}';

    // ── Resource class tokens ──────────────────────────────────────────────
    public const RESOURCE_COLUMNS                    = '{{resourceColumns}}';
    public const RESOURCE_FORM_FIELDS                = '{{resourceFormFields}}';
    public const RESOURCE_FILTERS                    = '{{resourceFilters}}';
    public const NAVIGATION_ICON                     = '{{navigationIcon}}';

    // ── Migration tokens ───────────────────────────────────────────────────
    /** Blueprint column definition lines for a generated migration file. */
    public const MIGRATION_COLUMNS                   = '{{migrationColumns}}';

    // ── Per-field partial tokens ───────────────────────────────────────────
    public const TITLE                               = '{{title}}';
    public const COLUMN                              = '{{column}}';
    public const DATATYPE                            = '{{datatype}}';

    // -----------------------------------------------------------------------
    // Static API
    // -----------------------------------------------------------------------

    /**
     * All registered token keys, grouped for clarity.
     *
     * @return array<string, string[]>
     */
    public static function groups(): array
    {
        return [
            self::GROUP_MODEL => [
                self::MODEL_NAME,
                self::MODEL_NAME_LOWER,
                self::MODEL_NAME_SINGULAR_LOWER,
                self::MODEL_PLURAL_NAME,
                self::MODEL_TITLE,
                self::MODEL_PLURAL_TITLE,
                self::MODEL_NAME_PLURAL_LOWER,
                self::MODEL_NAME_PLURAL_UPPER,
                self::MODEL_NAME_PLURAL_TITLE,
                self::MODEL_ROUTE,
                self::MODEL_VIEW,
                self::MODEL_NAMESPACE,
                self::CONTROLLER_NAMESPACE,
            ],
            self::GROUP_INPUT => [
                self::GET_NAME_INPUT,
                self::GET_NAME_INPUT_LOWER,
                self::GET_NAME_INPUT_PLURAL_LOWER,
                self::GET_TEMPLATE,
                self::GET_THEME,
                self::GET_THEME_INPUT,
                self::GET_THEME_INPUT_LOWER,
                self::THEME_LOWER,
                self::GET_MODULE_INPUT,
                self::GET_MODULE_INPUT_MODULE,
                self::GET_MODULE_INPUT_MODULE_NEW,
                self::GET_MODULE_INPUT_TITLE,
                self::GET_MODULE_INPUT_CLASS,
                self::GET_MODULE_INPUT_LOWER,
                self::GET_MODULE_INPUT_MODULE_LOWER_CASE,
                self::LAYOUT,
            ],
            self::GROUP_COMPONENT => [
                self::FILLABLE,
                self::UPDATE_FIELD,
                self::RESET_FIELDS,
                self::SHOW_FIELDS,
                self::EDIT_FIELDS,
                self::ADD_FIELDS,
                self::FIELDS_LIST,
                self::FACTORY,
                self::RULES,
                self::SEARCH,
                self::TEMPLATE_HEADERS,
                self::RELATIONS,
                self::PROPERTIES,
                self::SOFT_DELETES_NAMESPACE,
                self::SOFT_DELETES,
                self::MIGRATION_COLUMNS,
            ],
            self::GROUP_VIEW => [
                self::TABLE_HEADER,
                self::TABLE_BODY,
                self::VIEW_ROWS,
                self::FORM,
                self::SHOW,
                self::TYPE,
            ],
            self::GROUP_RESOURCE => [
                self::RESOURCE_COLUMNS,
                self::RESOURCE_FORM_FIELDS,
                self::RESOURCE_FILTERS,
                self::NAVIGATION_ICON,
                self::MODEL_NAME_PLURAL_TITLE,
            ],
            self::GROUP_FIELD => [
                self::TITLE,
                self::COLUMN,
                self::DATATYPE,
            ],
        ];
    }

    /**
     * Flat list of all registered token keys.
     *
     * @return string[]
     */
    public static function keys(): array
    {
        return array_merge(...array_values(self::groups()));
    }

    /**
     * Check whether a token key is registered.
     */
    public static function has(string $token): bool
    {
        return in_array($token, self::keys(), true);
    }

    /**
     * Describe a single token — returns the group it belongs to,
     * or null if unknown.
     */
    public static function groupOf(string $token): ?string
    {
        foreach (self::groups() as $group => $tokens) {
            if (in_array($token, $tokens, true)) {
                return $group;
            }
        }
        return null;
    }

    /**
     * Build the base replacement map from a generator command context.
     *
     * This mirrors (and should stay in sync with) `buildReplacements()` in
     * LivewireGeneratorCommand. Use it as the canonical source when you need
     * the map outside a command context (e.g. in tests).
     *
     * @param  object  $ctx  Any object exposing the generator's public state:
     *                       ->name, ->table, ->module, ->theme, ->menu,
     *                       ->modelNamespace, ->controllerNamespace, ->layout,
     *                       ->templateName, ->options[]
     * @return array<string, string>
     */
    public static function buildBaseMap(object $ctx): array
    {
        $name = $ctx->name ?? '';

        return [
            // ── Model / class ──────────────────────────────────────────────
            self::MODEL_NAME                  => $name,
            self::MODEL_NAME_LOWER            => Str::camel($name),
            self::MODEL_NAME_SINGULAR_LOWER   => Str::lower($name),
            self::MODEL_PLURAL_NAME           => Str::plural($name),
            self::MODEL_TITLE                 => Str::title(Str::snake($name, ' ')),
            self::MODEL_PLURAL_TITLE          => Str::title(Str::snake(Str::plural($name), ' ')),
            self::MODEL_NAME_PLURAL_LOWER     => Str::camel(Str::plural($name)),
            self::MODEL_NAME_PLURAL_UPPER     => ucfirst(Str::plural($name)),
            self::MODEL_NAME_PLURAL_TITLE     => Str::title(Str::snake(Str::plural($name), ' ')),
            self::MODEL_ROUTE                 => $ctx->options['route'] ?? Str::kebab(Str::plural($name)),
            self::MODEL_VIEW                  => Str::kebab($name),
            self::MODEL_NAMESPACE             => $ctx->modelNamespace ?? '',
            self::CONTROLLER_NAMESPACE        => $ctx->controllerNamespace ?? '',

            // ── Input arguments ────────────────────────────────────────────
            self::GET_NAME_INPUT              => $ctx->table ?? '',
            self::GET_NAME_INPUT_LOWER        => Str::lower($ctx->table ?? ''),
            self::GET_NAME_INPUT_PLURAL_LOWER => Str::lower(Str::plural($ctx->table ?? '')),
            self::GET_TEMPLATE                => $ctx->templateName ?? '',
            self::GET_THEME                   => $ctx->theme ?? '',
            self::GET_THEME_INPUT             => $ctx->theme ?? '',
            self::GET_THEME_INPUT_LOWER       => Str::lower($ctx->theme ?? ''),
            self::THEME_LOWER                 => Str::lower($ctx->theme ?? ''),
            self::GET_MODULE_INPUT            => Str::lower($ctx->module ?? ''),
            self::GET_MODULE_INPUT_MODULE     => $ctx->module ?? '',
            self::GET_MODULE_INPUT_MODULE_NEW => ucfirst($ctx->module ?? ''),
            self::GET_MODULE_INPUT_TITLE      => Str::title($ctx->module ?? ''),
            self::GET_MODULE_INPUT_CLASS      => Str::studly($ctx->module ?? ''),
            self::GET_MODULE_INPUT_LOWER      => Str::lower($ctx->module ?? ''),
            self::LAYOUT                      => $ctx->layout ?? 'layouts.app',
        ];
    }

    /**
     * Return an empty/placeholder map for every known token — useful for
     * validating that a stub has no unknown tokens, or as a dev reference.
     *
     * @return array<string, string>
     */
    public static function emptyMap(): array
    {
        return array_fill_keys(self::keys(), '');
    }

    /**
     * Scan a stub string and return any tokens that are NOT registered here.
     *
     * @return string[]  Unknown tokens found in the content.
     */
    public static function unknownTokens(string $stubContent): array
    {
        preg_match_all('/\{\{[a-zA-Z0-9_]+\}\}/', $stubContent, $matches);
        $found = array_unique($matches[0] ?? []);
        return array_values(array_filter($found, fn ($t) => !self::has($t)));
    }
}
