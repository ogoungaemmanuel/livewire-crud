<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Enums;

/**
 * Export formats supported by the CRUD generator.
 */
enum ExportFormat: string
{
    case Excel = 'excel';
    case Csv   = 'csv';
    case Pdf   = 'pdf';
    case Json  = 'json';

    // -----------------------------------------------------------------------
    // Metadata helpers
    // -----------------------------------------------------------------------

    public function label(): string
    {
        return match ($this) {
            self::Excel => 'Excel (XLSX)',
            self::Csv   => 'CSV',
            self::Pdf   => 'PDF',
            self::Json  => 'JSON',
        };
    }

    public function mimeType(): string
    {
        return match ($this) {
            self::Excel => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            self::Csv   => 'text/csv',
            self::Pdf   => 'application/pdf',
            self::Json  => 'application/json',
        };
    }

    public function extension(): string
    {
        return match ($this) {
            self::Excel => 'xlsx',
            self::Csv   => 'csv',
            self::Pdf   => 'pdf',
            self::Json  => 'json',
        };
    }

    // -----------------------------------------------------------------------
    // Collection helpers
    // -----------------------------------------------------------------------

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }

    /**
     * Return only formats enabled in package config.
     *
     * @return array<string>
     */
    public static function enabledValues(): array
    {
        $enabled = config('livewire-crud.export.formats', []);

        return array_values(
            array_filter(
                self::values(),
                static fn (string $v): bool => (bool) ($enabled[$v] ?? false)
            )
        );
    }

    /**
     * @return array<string, string>   value => label
     */
    public static function options(): array
    {
        $out = [];
        foreach (self::cases() as $case) {
            $out[$case->value] = $case->label();
        }

        return $out;
    }
}
