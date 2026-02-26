<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Commands;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

/**
 * crud:migration — Generate ONLY a database migration for an arbitrary table.
 *
 * Supports two modes:
 *   Interactive  – prompts for each column (identical UX to crud:new)
 *   Inline       – columns defined via --columns option
 *
 * Usage (interactive):
 *   php artisan crud:migration products Shop
 *
 * Usage (inline):
 *   php artisan crud:migration products Shop \
 *       --columns="name:string,description:text:nullable,price:decimal,status:enum:active:inactive:pending"
 *
 * Column definition format for --columns:
 *   <name>:<type>[:<values-for-enum>...][:nullable][,...]
 *
 *   Examples:
 *     name:string
 *     slug:varchar100:nullable
 *     price:decimal
 *     status:enum:active:inactive:pending
 *     status:enum:active:inactive:nullable    ← nullable enum
 *     notes:text:nullable
 *
 * Available short-hand types (same as interactive menu):
 *   string | varchar255 | varchar100 | varchar50 | text | longtext
 *   integer | int | bigint | biginteger
 *   fk | unsignedbiginteger
 *   boolean | bool | tinyint
 *   decimal | decimal84 | decimal154
 *   float | double
 *   date | datetime | timestamp | time
 *   enum
 *   json | uuid
 *
 * Extra options:
 *   --with-softdeletes   Append $table->softDeletes()
 *   --with-status        Prepend status enum(active,inactive,pending) column
 *   --with-order         Prepend sort_order unsignedInteger column
 */
class LivewireMigrationGenerator extends LivewireGeneratorCommand
{
    // -----------------------------------------------------------------------
    // Column type map  (short-hand → raw DB type for TypeMapper)
    // -----------------------------------------------------------------------

    /** @var array<string, string> */
    private const TYPE_MAP = [
        'string'             => 'varchar(255)',
        'varchar255'         => 'varchar(255)',
        'varchar100'         => 'varchar(100)',
        'varchar50'          => 'varchar(50)',
        'text'               => 'text',
        'longtext'           => 'longtext',
        'longText'           => 'longtext',
        'integer'            => 'integer',
        'int'                => 'integer',
        'biginteger'         => 'bigint',
        'bigint'             => 'bigint',
        'BigInteger'         => 'bigint',
        'fk'                 => 'unsignedBigInteger',
        'unsignedbiginteger' => 'unsignedBigInteger',
        'boolean'            => 'tinyint(1)',
        'bool'               => 'tinyint(1)',
        'tinyint'            => 'tinyint(1)',
        'decimal'            => 'decimal(8,2)',
        'decimal84'          => 'decimal(8,2)',
        'decimal154'         => 'decimal(15,4)',
        'float'              => 'float',
        'double'             => 'double',
        'date'               => 'date',
        'datetime'           => 'datetime',
        'timestamp'          => 'timestamp',
        'time'               => 'time',
        'enum'               => 'enum',
        'json'               => 'json',
        'uuid'               => 'uuid',
    ];

    /** @var array<string, string>  Human-readable labels for interactive menu */
    private const COLUMN_TYPES = [
        'string (varchar 255)'        => 'varchar(255)',
        'string (varchar 100)'        => 'varchar(100)',
        'string (varchar 50)'         => 'varchar(50)',
        'text'                        => 'text',
        'longText'                    => 'longtext',
        'integer'                     => 'integer',
        'bigInteger'                  => 'bigint',
        'unsignedBigInteger (FK _id)' => 'unsignedBigInteger',
        'tinyInteger / boolean'       => 'tinyint(1)',
        'decimal (8,2)'               => 'decimal(8,2)',
        'decimal (15,4)'              => 'decimal(15,4)',
        'float'                       => 'float',
        'double'                      => 'double',
        'date'                        => 'date',
        'dateTime'                    => 'datetime',
        'timestamp'                   => 'timestamp',
        'time'                        => 'time',
        'enum'                        => 'enum',
        'json'                        => 'json',
        'uuid'                        => 'uuid',
    ];

    // -----------------------------------------------------------------------
    // Command definition
    // -----------------------------------------------------------------------

    protected $signature = 'crud:migration
        {table  : Snake-case table name (e.g. products)}
        {module : Module name (e.g. Shop)}
        {--columns=  : Inline column defs: name:string,price:decimal,status:enum:active:inactive}
        {--with-softdeletes : Append $table->softDeletes()}
        {--with-status      : Prepend status enum(active,inactive,pending) column}
        {--with-order       : Prepend sort_order unsignedInteger column}';

    protected $description = 'Generate a Laravel migration file for any table (interactive or inline column definitions)';

    // -----------------------------------------------------------------------
    // handle()
    // -----------------------------------------------------------------------

    /** @throws \Illuminate\Contracts\Filesystem\FileNotFoundException */
    public function handle(): int
    {
        $this->table     = $this->argument('table');
        $this->module    = $this->argument('module');
        $this->modelName = Str::studly(Str::singular($this->table));

        $this->info("Generating migration for table: <comment>{$this->table}</comment>");
        $this->info("Module: <comment>{$this->module}</comment>");
        $this->newLine();

        // ── Collect columns ────────────────────────────────────────────────
        $columns = $this->option('columns')
            ? $this->parseInlineColumns((string) $this->option('columns'))
            : $this->collectColumns();

        if (empty($columns)) {
            $this->error('No columns defined. Aborting.');
            return self::FAILURE;
        }

        // ── Inject extra convenience columns ──────────────────────────────
        if ($this->option('with-status')) {
            array_unshift($columns, (object) [
                'Field'   => 'status',
                'Type'    => "enum('active','inactive','pending')",
                'Null'    => 'NO',
                'Default' => 'active',
                'Key'     => '',
                'Extra'   => '',
            ]);
            $this->line("  <info>✓</info> status enum(active,inactive,pending) default=active <fg=gray>[--with-status]</>");
        }

        if ($this->option('with-order')) {
            $columns[] = (object) [
                'Field'   => 'sort_order',
                'Type'    => 'integer',
                'Null'    => 'YES',
                'Default' => '0',
                'Key'     => '',
                'Extra'   => '',
            ];
            $this->line("  <info>✓</info> sort_order integer default=0 <fg=gray>[--with-order]</>");
        }

        if ($this->option('with-softdeletes')) {
            $columns[] = (object) [
                'Field'   => 'deleted_at',
                'Type'    => 'timestamp',
                'Null'    => 'YES',
                'Default' => null,
                'Key'     => '',
                'Extra'   => '',
            ];
            $this->line("  <info>✓</info> deleted_at (softDeletes) <fg=gray>[--with-softdeletes]</>");
        }

        // Pre-load columns so parent never queries the DB
        $this->setColumns($columns);

        // ── Show summary ───────────────────────────────────────────────────
        $this->newLine();
        $this->line('<options=bold>Migration schema</>');
        $this->table(
            ['#', 'Column', 'Type', 'Nullable', 'Default'],
            array_map(fn (int $i, object $c) => [
                $i + 1,
                $c->Field,
                $c->Type,
                $c->Null === 'YES' ? 'YES' : 'NO',
                $c->Default ?? '',
            ], array_keys($columns), $columns)
        );
        $this->newLine();

        // ── Generate migration ─────────────────────────────────────────────
        $this->buildMigration();

        $this->newLine();
        $this->info('<options=bold>Migration generated successfully.</>');
        $this->comment('Run <info>php artisan migrate</info> to apply.');

        return self::SUCCESS;
    }

    // -----------------------------------------------------------------------
    // Override getters — parent reads from argument('name') / argument('module')
    // but this command uses argument('table') for the table name
    // -----------------------------------------------------------------------

    protected function getNameInput(): string
    {
        return trim($this->argument('table'));
    }

    protected function getModuleInput(): string
    {
        return trim($this->argument('module'), '{}');
    }

    /** Not used in migration-only generation; return a safe default. */
    protected function getThemeInput(): string
    {
        return 'none';
    }

    /** Not used in migration-only generation; return a safe default. */
    protected function getMenuInput(): string
    {
        return '';
    }

    // -----------------------------------------------------------------------
    // Abstract method stubs (not used for migration-only generation)
    // -----------------------------------------------------------------------

    public function buildModel(): static
    {
        return $this;
    }

    public function buildViews(): static
    {
        return $this;
    }

    // -----------------------------------------------------------------------
    // Inline column parser
    // -----------------------------------------------------------------------

    /**
     * Parse the --columns option string into column descriptor objects.
     *
     * Format per column:  <name>:<type>[:<enum-val>...][:nullable]
     *
     * Examples:
     *   name:string
     *   price:decimal:nullable
     *   status:enum:active:inactive:pending
     *   status:enum:active:inactive:pending:nullable
     *
     * @return array<object>
     */
    private function parseInlineColumns(string $raw): array
    {
        $columns = [];

        foreach (explode(',', $raw) as $def) {
            $def = trim($def);
            if ($def === '') {
                continue;
            }

            $parts    = explode(':', $def);
            $field    = Str::snake(trim(array_shift($parts)));
            $typeKey  = strtolower(trim(array_shift($parts) ?? 'string'));

            // Resolve nullable flag — last segment may be 'nullable'
            $nullable = false;
            if (isset($parts[array_key_last($parts)])
                && strtolower($parts[array_key_last($parts)]) === 'nullable') {
                $nullable = true;
                array_pop($parts);
            }

            // Resolve raw DB type
            $rawType = self::TYPE_MAP[$typeKey] ?? self::TYPE_MAP[strtolower($typeKey)] ?? 'varchar(255)';

            // For enum: remaining $parts are the values
            if ($rawType === 'enum') {
                if (empty($parts)) {
                    $parts = ['active', 'inactive'];
                    $this->warn("  No enum values provided for '{$field}' — defaulting to active,inactive");
                }
                $quoted  = implode(',', array_map(fn ($v) => "'" . trim($v) . "'", $parts));
                $rawType = "enum({$quoted})";
            }

            $columns[] = (object) [
                'Field'   => $field,
                'Type'    => $rawType,
                'Null'    => $nullable ? 'YES' : 'NO',
                'Default' => null,
                'Key'     => '',
                'Extra'   => '',
            ];

            $this->line("  <info>✓</info> {$field} ({$rawType})" . ($nullable ? ' nullable' : ''));
        }

        return $columns;
    }

    // -----------------------------------------------------------------------
    // Interactive column builder (identical UX to crud:new)
    // -----------------------------------------------------------------------

    /**
     * Prompt the developer for each column definition.
     *
     * @return array<object>
     */
    private function collectColumns(): array
    {
        $this->line('<options=bold>Define table columns</> (leave name blank to finish)');
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

            if (in_array($field, array_column($columns, 'Field'), true)) {
                $this->warn("  Column '{$field}' already defined — skipping.");
                continue;
            }

            // ── Type ───────────────────────────────────────────────────────
            $typeLabels = array_keys(self::COLUMN_TYPES);
            $typeChoice = $this->choice("  Type for <comment>{$field}</comment>", $typeLabels, 0);
            $rawType    = self::COLUMN_TYPES[$typeChoice];

            // Handle enum values
            if ($rawType === 'enum') {
                $enumValues = $this->ask(
                    "  Enum values for {$field} (comma-separated, e.g. active,inactive,pending)",
                    'active,inactive'
                );
                $quoted  = implode(',', array_map(
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

            $columns[] = (object) [
                'Field'   => $field,
                'Type'    => $rawType,
                'Null'    => $nullable ? 'YES' : 'NO',
                'Default' => $defaultValue,
                'Key'     => '',
                'Extra'   => '',
            ];

            $this->line(
                "  <info>✓</info> {$field} ({$rawType})"
                . ($nullable ? ' nullable' : '')
                . ($defaultValue !== null ? " default={$defaultValue}" : '')
            );
            $this->newLine();
            $index++;
        }

        return $columns;
    }
}
