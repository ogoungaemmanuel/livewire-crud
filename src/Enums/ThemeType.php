<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Enums;

/**
 * Represents the UI theme modes available to the CRUD generator.
 */
enum ThemeType: string
{
    /** Bare Livewire component — no wrapping theme layout. */
    case None = 'none';

    /** Bare Livewire component using the package's default Bootstrap layout. */
    case NoneDefault = 'nonedefault';

    /** Full default admin theme (Bootstrap 5 + Alpine). */
    case Default = 'default';

    /** Modern card-based admin theme (Bootstrap 5 + Alpine + ApexCharts). */
    case Modern = 'modern';

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    public function label(): string
    {
        return match ($this) {
            self::None        => 'No Theme',
            self::NoneDefault => 'Default Layout (no theme namespace)',
            self::Default     => 'Default Admin Theme',
            self::Modern      => 'Modern Admin Theme',
        };
    }

    /** Returns true when the theme requires a dedicated theme namespace directory. */
    public function isThemed(): bool
    {
        return match ($this) {
            self::None, self::NoneDefault => false,
            default                       => true,
        };
    }

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
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
