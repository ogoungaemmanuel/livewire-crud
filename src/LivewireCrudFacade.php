<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud;

use Illuminate\Support\Facades\Facade;
use Xslainadmin\LivewireCrud\Columns;
use Xslainadmin\LivewireCrud\Fields;
use Xslainadmin\LivewireCrud\Schema;

/**
 * @method static string        version()
 * @method static bool          isFeatureEnabled(string $feature)
 * @method static array<string> enabledFeatures()
 * @method static array<string> availableThemes()
 * @method static string        defaultTheme()
 * @method static array<string> availableExportFormats()
 * @method static mixed         config(string $key, mixed $default = null)
 * @method static string        stubPath()
 *
 * Column factories
 * @method static Columns\TextColumn    textColumn(string $name)
 * @method static Columns\BadgeColumn   badgeColumn(string $name)
 * @method static Columns\DateColumn    dateColumn(string $name)
 * @method static Columns\BooleanColumn booleanColumn(string $name)
 * @method static Columns\ImageColumn   imageColumn(string $name)
 * @method static Columns\NumberColumn  numberColumn(string $name)
 *
 * Field factories
 * @method static Fields\TextInput   textInput(string $name)
 * @method static Fields\Textarea    textarea(string $name)
 * @method static Fields\SelectInput selectInput(string $name)
 * @method static Fields\DatePicker  datePicker(string $name)
 * @method static Fields\Toggle      toggle(string $name)
 * @method static Fields\FileUpload  fileUpload(string $name)
 * @method static Fields\RichEditor  richEditor(string $name)
 *
 * Schema factories
 * @method static Schema\FormSchema  form()
 * @method static Schema\TableSchema table()
 *
 * @see \Xslainadmin\LivewireCrud\LivewireCrud
 */
class LivewireCrudFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'livewire-crud';
    }
}
