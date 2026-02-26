<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Support;

/**
 * Maps database column metadata to the appropriate Column / Field builder class names
 * and input types.
 *
 * Usage:
 *   $mapper  = new TypeMapper();
 *   $colClass = $mapper->columnClass('is_active', 'tinyint(1)');  // 'BooleanColumn'
 *   $fldClass = $mapper->fieldClass('body', 'longtext');           // 'RichEditor'
 *   $htmlType = $mapper->htmlInputType('created_at', 'timestamp'); // 'date'
 */
class TypeMapper
{
    // -----------------------------------------------------------------------
    // Column class mapping
    // -----------------------------------------------------------------------

    /**
     * Return the unqualified column class name for the given DB column.
     *
     * @param  string $field  Column name
     * @param  string $type   Raw DB type string (e.g. 'varchar(255)', 'tinyint(1)')
     */
    public function columnClass(string $field, string $type): string
    {
        $type = strtolower($type);
        $field = strtolower($field);

        if ($this->isImageField($field)) {
            return 'ImageColumn';
        }

        if ($this->isBooleanType($type, $field)) {
            return 'BooleanColumn';
        }

        if ($this->isNumericType($type) && !str_ends_with($field, '_id')) {
            return 'NumberColumn';
        }

        if ($this->isDateTimeType($type)) {
            return 'DateColumn';
        }

        if ($this->isDateType($type)) {
            return 'DateColumn';
        }

        if ($this->isStatusField($field)) {
            return 'BadgeColumn';
        }

        return 'TextColumn';
    }

    /**
     * Return fluent chain modifiers to append to the column constructor call.
     *
     * @return string  e.g. '->dateTime()->sortable()'
     */
    public function columnChain(string $field, string $type): string
    {
        $type  = strtolower($type);
        $field = strtolower($field);

        if ($this->isDateTimeType($type)) {
            return "->dateTime()->sortable()";
        }
        if ($this->isDateType($type)) {
            return "->sortable()";
        }
        if ($this->isNumericType($type) && !str_ends_with($field, '_id')) {
            return "->numeric(2)";
        }
        if ($this->isStatusField($field)) {
            return "->searchable()";
        }

        return "->searchable()->sortable()";
    }

    // -----------------------------------------------------------------------
    // Field class mapping
    // -----------------------------------------------------------------------

    /**
     * Return the unqualified field class name for the given DB column.
     */
    public function fieldClass(string $field, string $type): string
    {
        $type  = strtolower($type);
        $field = strtolower($field);

        if ($this->isImageField($field)) {
            return 'FileUpload';
        }
        if ($this->isBooleanType($type, $field)) {
            return 'Toggle';
        }
        if ($this->isTextAreaType($type)) {
            return 'RichEditor';
        }
        if ($this->isDateTimeType($type)) {
            return 'DatePicker';
        }
        if ($this->isDateType($type)) {
            return 'DatePicker';
        }
        if ($field === 'email') {
            return 'TextInput';      // handled with ->email()
        }
        if ($field === 'password') {
            return 'TextInput';      // handled with ->password()
        }
        if (str_contains($field, 'url') || str_contains($field, 'website')) {
            return 'TextInput';
        }
        if (str_contains($field, 'phone') || str_contains($field, 'mobile')) {
            return 'TextInput';
        }
        if ($this->isNumericType($type) && !str_ends_with($field, '_id')) {
            return 'TextInput';      // handled with ->numeric()
        }
        if (str_ends_with($field, '_id')) {
            return 'SelectInput';
        }

        return 'TextInput';
    }

    /**
     * Return fluent chain modifiers for the field constructor call.
     */
    public function fieldChain(string $field, string $type, bool $required): string
    {
        $type  = strtolower($type);
        $field = strtolower($field);
        $req   = $required ? '->required()' : '';

        if ($this->isImageField($field)) {
            return "->image(){$req}";
        }
        if ($this->isBooleanType($type, $field)) {
            return '';
        }
        if ($this->isTextAreaType($type)) {
            return "{$req}->rows(5)";
        }
        if ($this->isDateTimeType($type)) {
            return "{$req}->withTime()";
        }
        if ($this->isDateType($type)) {
            return $req;
        }
        if ($field === 'email') {
            return "{$req}->email()";
        }
        if ($field === 'password') {
            return "->required()->password()";
        }
        if (str_contains($field, 'url') || str_contains($field, 'website')) {
            return "{$req}->url()";
        }
        if (str_contains($field, 'phone') || str_contains($field, 'mobile')) {
            return "{$req}->tel()";
        }
        if ($this->isNumericType($type) && !str_ends_with($field, '_id')) {
            return "{$req}->numeric()";
        }

        return $req;
    }

    // -----------------------------------------------------------------------
    // HTML input type mapping (for Blade view stubs)
    // -----------------------------------------------------------------------

    /**
     * Return the HTML <input type="..."> value for a DB column.
     */
    public function htmlInputType(string $field, string $type): string
    {
        $type  = strtolower($type);
        $field = strtolower($field);

        if ($this->isDateTimeType($type)) {
            return 'datetime-local';
        }
        if ($this->isDateType($type)) {
            return 'date';
        }
        if (str_contains($type, 'time')) {
            return 'time';
        }
        if ($this->isTextAreaType($type)) {
            return 'textarea';
        }
        if ($this->isNumericType($type)) {
            return 'number';
        }
        if ($this->isBooleanType($type, $field)) {
            return 'checkbox';
        }
        if (str_contains($type, 'enum')) {
            return 'select';
        }
        if (str_contains($field, 'email')) {
            return 'email';
        }
        if ($field === 'password') {
            return 'password';
        }

        return 'text';
    }

    // -----------------------------------------------------------------------
    // Migration column-definition generator
    // -----------------------------------------------------------------------

    /**
     * Return a full Laravel Blueprint column call for a DB column.
     *
     * Example output:
     *   $table->string('name', 100)->nullable()
     *   $table->unsignedBigInteger('user_id')
     *   $table->decimal('price', 8, 2)->default(0)
     *
     * @param  string      $field     Column name
     * @param  string      $type      Raw DB type (e.g. 'varchar(255)', 'tinyint(1)')
     * @param  bool        $nullable  Whether the column allows NULL
     * @param  mixed       $default   Default value (null = no default)
     * @return string
     */
    public function migrationColumnDefinition(
        string $field,
        string $type,
        bool   $nullable,
        mixed  $default
    ): string {
        $def = $this->migrationMethod($field, strtolower($type));

        if ($nullable) {
            $def .= '->nullable()';
        }

        if ($default !== null && $default !== '') {
            if (is_numeric($default)) {
                $def .= "->default({$default})";
            } elseif (in_array(strtoupper((string) $default), ['CURRENT_TIMESTAMP', 'NOW()'], true)) {
                $def .= "->useCurrent()";
            } else {
                $escaped = addslashes((string) $default);
                $def .= "->default('{$escaped}')";
            }
        }

        return $def;
    }

    /**
     * Map a raw DB type string to the matching Laravel Blueprint method call.
     * Does NOT include nullable / default chains — those are appended by
     * migrationColumnDefinition().
     */
    private function migrationMethod(string $field, string $type): string
    {
        // Foreign key columns → unsignedBigInteger
        if (str_ends_with($field, '_id')) {
            return "\$table->unsignedBigInteger('{$field}')";
        }

        // varchar(n) → string('field') or string('field', n)
        if (preg_match('/^varchar\((\d+)\)$/', $type, $m)) {
            $len = (int) $m[1];
            return $len === 255
                ? "\$table->string('{$field}')"
                : "\$table->string('{$field}', {$len})";
        }

        // char(n)
        if (preg_match('/^char\((\d+)\)$/', $type, $m)) {
            return "\$table->char('{$field}', {$m[1]})";
        }

        // tinyint(1) / boolean → boolean
        if (preg_match('/^tinyint\(1\)/', $type) || in_array($type, ['boolean', 'bool'], true)) {
            return "\$table->boolean('{$field}')";
        }

        // decimal(m,d)
        if (preg_match('/^decimal\((\d+),(\d+)\)$/', $type, $m)) {
            return "\$table->decimal('{$field}', {$m[1]}, {$m[2]})";
        }

        // float(m,d)
        if (preg_match('/^float\((\d+),(\d+)\)$/', $type, $m)) {
            return "\$table->float('{$field}', {$m[1]}, {$m[2]})";
        }

        // double(m,d)
        if (preg_match('/^double\((\d+),(\d+)\)$/', $type, $m)) {
            return "\$table->double('{$field}', {$m[1]}, {$m[2]})";
        }

        // enum('a','b','c')
        if (preg_match("/^enum\((.+)\)$/", $type, $m)) {
            return "\$table->enum('{$field}', [{$m[1]}])";
        }

        // integer variants (order: most specific first)
        if (str_starts_with($type, 'bigint') || $type === 'bigint') {
            return "\$table->bigInteger('{$field}')";
        }
        if (str_starts_with($type, 'mediumint') || $type === 'mediumint') {
            return "\$table->mediumInteger('{$field}')";
        }
        if (str_starts_with($type, 'smallint') || $type === 'smallint') {
            return "\$table->smallInteger('{$field}')";
        }
        if (str_starts_with($type, 'tinyint') || $type === 'tinyint') {
            return "\$table->tinyInteger('{$field}')";
        }
        if (str_contains($type, 'int')) {
            return "\$table->integer('{$field}')";
        }

        // float / double / decimal
        if ($type === 'float') {
            return "\$table->float('{$field}')";
        }
        if ($type === 'double') {
            return "\$table->double('{$field}')";
        }
        if (str_contains($type, 'decimal') || str_contains($type, 'numeric')) {
            return "\$table->decimal('{$field}')";
        }

        // text variants
        if (str_contains($type, 'longtext')) {
            return "\$table->longText('{$field}')";
        }
        if (str_contains($type, 'mediumtext')) {
            return "\$table->mediumText('{$field}')";
        }
        if (str_contains($type, 'text')) {
            return "\$table->text('{$field}')";
        }

        // date & time
        if ($type === 'date') {
            return "\$table->date('{$field}')";
        }
        if (str_contains($type, 'datetime')) {
            return "\$table->dateTime('{$field}')";
        }
        if (str_contains($type, 'timestamp')) {
            return "\$table->timestamp('{$field}')";
        }
        if ($type === 'time') {
            return "\$table->time('{$field}')";
        }
        if ($type === 'year') {
            return "\$table->year('{$field}')";
        }

        // JSON / binary / uuid
        if (str_contains($type, 'json')) {
            return "\$table->json('{$field}')";
        }
        if (str_contains($type, 'binary') || str_contains($type, 'blob')) {
            return "\$table->binary('{$field}')";
        }
        if ($type === 'uuid') {
            return "\$table->uuid('{$field}')";
        }

        // fallback
        return "\$table->string('{$field}')";
    }

    // -----------------------------------------------------------------------
    // Private detection helpers
    // -----------------------------------------------------------------------

    private function isImageField(string $field): bool
    {
        return str_contains($field, 'image')
            || str_contains($field, 'photo')
            || str_contains($field, 'avatar')
            || str_contains($field, 'thumbnail')
            || str_contains($field, 'picture')
            || str_contains($field, 'logo');
    }

    private function isBooleanType(string $type, string $field): bool
    {
        return str_contains($type, 'tinyint(1)')
            || str_contains($type, 'boolean')
            || str_starts_with($field, 'is_')
            || str_starts_with($field, 'has_')
            || str_starts_with($field, 'can_')
            || str_starts_with($field, 'show_');
    }

    private function isNumericType(string $type): bool
    {
        return str_contains($type, 'decimal')
            || str_contains($type, 'float')
            || str_contains($type, 'double')
            || str_contains($type, 'numeric')
            || $type === 'int'
            || $type === 'integer'
            || $type === 'bigint'
            || $type === 'tinyint'
            || $type === 'smallint'
            || $type === 'mediumint'
            || str_starts_with($type, 'int(')
            || str_starts_with($type, 'bigint(')
            || str_starts_with($type, 'smallint(')
            || str_starts_with($type, 'mediumint(');
    }

    private function isDateTimeType(string $type): bool
    {
        return str_contains($type, 'datetime')
            || str_contains($type, 'timestamp');
    }

    private function isDateType(string $type): bool
    {
        return $type === 'date'
            || str_starts_with($type, 'date(')
            || $type === 'time';
    }

    private function isTextAreaType(string $type): bool
    {
        return str_contains($type, 'text')
            || str_contains($type, 'longtext')
            || str_contains($type, 'mediumtext')
            || str_contains($type, 'json');
    }

    private function isStatusField(string $field): bool
    {
        return str_contains($field, 'status')
            || str_contains($field, 'type')
            || str_contains($field, 'role')
            || str_contains($field, 'state');
    }
}
