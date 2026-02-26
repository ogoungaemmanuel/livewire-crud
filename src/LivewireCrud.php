<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud;

use Xslainadmin\LivewireCrud\Enums\ExportFormat;
use Xslainadmin\LivewireCrud\Enums\ThemeType;

/**
 * Main entry-point class, bound to the 'livewire-crud' container key and
 * surfaced through the LivewireCrudFacade.
 */
final class LivewireCrud
{
    /** Semantic version of this package. */
    public const VERSION = '4.1.0';

    // -----------------------------------------------------------------------
    // Version helpers
    // -----------------------------------------------------------------------

    public function version(): string
    {
        return self::VERSION;
    }

    // -----------------------------------------------------------------------
    // Feature flag helpers
    // -----------------------------------------------------------------------

    /** Check whether a named feature is enabled in the package config. */
    public function isFeatureEnabled(string $feature): bool
    {
        return (bool) config("livewire-crud.features.{$feature}", false);
    }

    /**
     * Retrieve all enabled feature keys.
     *
     * @return array<string>
     */
    public function enabledFeatures(): array
    {
        $features = config('livewire-crud.features', []);

        return array_keys(array_filter($features));
    }

    // -----------------------------------------------------------------------
    // Theme helpers
    // -----------------------------------------------------------------------

    /** All available theme values (from the ThemeType enum). */
    public function availableThemes(): array
    {
        return ThemeType::values();
    }

    public function defaultTheme(): string
    {
        return config('livewire-crud.ui.default_theme', ThemeType::Default->value);
    }

    // -----------------------------------------------------------------------
    // Export helpers
    // -----------------------------------------------------------------------

    /** All available export formats (from the ExportFormat enum). */
    public function availableExportFormats(): array
    {
        return ExportFormat::enabledValues();
    }

    // -----------------------------------------------------------------------
    // Config accessors
    // -----------------------------------------------------------------------

    /**
     * Retrieve any top-level package config key with an optional default.
     */
    public function config(string $key, mixed $default = null): mixed
    {
        return config("livewire-crud.{$key}", $default);
    }

    /**
     * Return the resolved stub path (custom or bundled default).
     */
    public function stubPath(): string
    {
        $path = config('livewire-crud.stub_path', 'default');

        return $path === 'default'
            ? rtrim(dirname(__DIR__).'/src/stubs', '/')
            : rtrim($path, '/');
    }

    // -----------------------------------------------------------------------
    // Builder factory shortcuts
    // -----------------------------------------------------------------------

    /** @return \Xslainadmin\LivewireCrud\Columns\TextColumn */
    public static function textColumn(string $name): Columns\TextColumn
    {
        return Columns\TextColumn::make($name);
    }

    /** @return \Xslainadmin\LivewireCrud\Columns\BadgeColumn */
    public static function badgeColumn(string $name): Columns\BadgeColumn
    {
        return Columns\BadgeColumn::make($name);
    }

    /** @return \Xslainadmin\LivewireCrud\Columns\DateColumn */
    public static function dateColumn(string $name): Columns\DateColumn
    {
        return Columns\DateColumn::make($name);
    }

    /** @return \Xslainadmin\LivewireCrud\Columns\BooleanColumn */
    public static function booleanColumn(string $name): Columns\BooleanColumn
    {
        return Columns\BooleanColumn::make($name);
    }

    /** @return \Xslainadmin\LivewireCrud\Columns\ImageColumn */
    public static function imageColumn(string $name): Columns\ImageColumn
    {
        return Columns\ImageColumn::make($name);
    }

    /** @return \Xslainadmin\LivewireCrud\Columns\NumberColumn */
    public static function numberColumn(string $name): Columns\NumberColumn
    {
        return Columns\NumberColumn::make($name);
    }

    /** @return \Xslainadmin\LivewireCrud\Fields\TextInput */
    public static function textInput(string $name): Fields\TextInput
    {
        return Fields\TextInput::make($name);
    }

    /** @return \Xslainadmin\LivewireCrud\Fields\Textarea */
    public static function textarea(string $name): Fields\Textarea
    {
        return Fields\Textarea::make($name);
    }

    /** @return \Xslainadmin\LivewireCrud\Fields\SelectInput */
    public static function selectInput(string $name): Fields\SelectInput
    {
        return Fields\SelectInput::make($name);
    }

    /** @return \Xslainadmin\LivewireCrud\Fields\DatePicker */
    public static function datePicker(string $name): Fields\DatePicker
    {
        return Fields\DatePicker::make($name);
    }

    /** @return \Xslainadmin\LivewireCrud\Fields\Toggle */
    public static function toggle(string $name): Fields\Toggle
    {
        return Fields\Toggle::make($name);
    }

    /** @return \Xslainadmin\LivewireCrud\Fields\FileUpload */
    public static function fileUpload(string $name): Fields\FileUpload
    {
        return Fields\FileUpload::make($name);
    }

    /** @return \Xslainadmin\LivewireCrud\Fields\RichEditor */
    public static function richEditor(string $name): Fields\RichEditor
    {
        return Fields\RichEditor::make($name);
    }

    /** @return \Xslainadmin\LivewireCrud\Schema\FormSchema */
    public static function form(): Schema\FormSchema
    {
        return Schema\FormSchema::make();
    }

    /** @return \Xslainadmin\LivewireCrud\Schema\TableSchema */
    public static function table(): Schema\TableSchema
    {
        return Schema\TableSchema::make();
    }
}
