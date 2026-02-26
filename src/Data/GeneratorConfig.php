<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Data;

use Spatie\LaravelData\Data;
use Xslainadmin\LivewireCrud\Enums\ThemeType;

/**
 * Typed, immutable configuration object passed to generator commands.
 *
 * Using spatie/laravel-data means this DTO can be serialised, validated,
 * and cast automatically — ensuring type-safety across the entire generator
 * pipeline instead of relying on plain arrays.
 */
final class GeneratorConfig extends Data
{
    public function __construct(
        /** Database table name that drives the generation. */
        public readonly string $tableName,

        /** Laravel module name (e.g. "Inventory", "HR"). */
        public readonly string $module,

        /** Theme variant to use for generated views. */
        public readonly ThemeType $theme,

        /** Navigation-menu group key (maps to a menu blade .sub file). */
        public readonly string $menu,

        // -----------------------------------------------------------------------
        // Namespace overrides
        // -----------------------------------------------------------------------

        /** Fully-qualified model namespace (default: App\Models). */
        public readonly string $modelNamespace = 'App\\Models',

        // -----------------------------------------------------------------------
        // Generation feature toggles
        // -----------------------------------------------------------------------

        public readonly bool $generateExport       = true,
        public readonly bool $generateImport        = true,
        public readonly bool $generateChart         = true,
        public readonly bool $generateCalendar      = true,
        public readonly bool $generateNotification  = true,
        public readonly bool $generateEmail         = true,
        public readonly bool $generatePrint         = true,
        public readonly bool $generatePdfExport     = true,
        public readonly bool $generateUpload        = true,
        public readonly bool $generateFactory       = true,

        // -----------------------------------------------------------------------
        // Model feature toggles
        // -----------------------------------------------------------------------

        public readonly bool $withSoftDeletes   = true,
        public readonly bool $withActivityLog   = true,
        public readonly bool $withScoutSearch   = false,
        public readonly bool $withQueryBuilder  = true,
    ) {}

    // -----------------------------------------------------------------------
    // Named constructors
    // -----------------------------------------------------------------------

    /**
     * Build a GeneratorConfig from a raw array (e.g. from Artisan command args).
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            tableName:             $data['table_name'],
            module:                $data['module'],
            theme:                 ThemeType::from($data['theme'] ?? ThemeType::Default->value),
            menu:                  $data['menu'] ?? 'admin',
            modelNamespace:        $data['model_namespace'] ?? 'App\\Models',
            generateExport:        (bool) ($data['generate_export']       ?? true),
            generateImport:        (bool) ($data['generate_import']       ?? true),
            generateChart:         (bool) ($data['generate_chart']        ?? true),
            generateCalendar:      (bool) ($data['generate_calendar']     ?? true),
            generateNotification:  (bool) ($data['generate_notification'] ?? true),
            generateEmail:         (bool) ($data['generate_email']        ?? true),
            generatePrint:         (bool) ($data['generate_print']        ?? true),
            generatePdfExport:     (bool) ($data['generate_pdf_export']   ?? true),
            generateUpload:        (bool) ($data['generate_upload']       ?? true),
            generateFactory:       (bool) ($data['generate_factory']      ?? true),
            withSoftDeletes:       (bool) ($data['with_soft_deletes']     ?? true),
            withActivityLog:       (bool) ($data['with_activity_log']     ?? true),
            withScoutSearch:       (bool) ($data['with_scout_search']     ?? false),
            withQueryBuilder:      (bool) ($data['with_query_builder']    ?? true),
        );
    }

    // -----------------------------------------------------------------------
    // Convenience accessors
    // -----------------------------------------------------------------------

    public function moduleLower(): string
    {
        return strtolower($this->module);
    }

    public function isThemed(): bool
    {
        return $this->theme->isThemed();
    }

    public function themeValue(): string
    {
        return $this->theme->value;
    }
}
