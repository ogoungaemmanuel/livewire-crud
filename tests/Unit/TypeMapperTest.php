<?php

declare(strict_types=1);

use Xslainadmin\LivewireCrud\Support\TypeMapper;

beforeEach(function () {
    $this->mapper = new TypeMapper();
});

// -----------------------------------------------------------------------
// columnClass()
// -----------------------------------------------------------------------

it('maps boolean types to BooleanColumn', function (string $type) {
    expect($this->mapper->columnClass('is_active', $type))->toBe('BooleanColumn');
})->with(['tinyint(1)', 'boolean', 'bool']);

it('maps date/time types to DateColumn', function (string $type) {
    expect($this->mapper->columnClass('published_at', $type))->toBe('DateColumn');
})->with(['date', 'datetime', 'timestamp', 'time']);

it('maps integer types to NumberColumn', function (string $type) {
    expect($this->mapper->columnClass('views', $type))->toBe('NumberColumn');
})->with(['int', 'bigint', 'integer', 'tinyint', 'smallint', 'mediumint']);

it('maps decimal/float types to NumberColumn', function (string $type) {
    expect($this->mapper->columnClass('price', $type))->toBe('NumberColumn');
})->with(['decimal', 'float', 'double', 'numeric']);

it('maps image column names to ImageColumn', function (string $field) {
    expect($this->mapper->columnClass($field, 'varchar'))->toBe('ImageColumn');
})->with(['image', 'avatar', 'photo', 'thumbnail', 'picture', 'logo']);

it('maps text/longtext types to TextColumn', function (string $type) {
    expect($this->mapper->columnClass('body', $type))->toBe('TextColumn');
})->with(['varchar', 'text', 'longtext', 'char', 'mediumtext']);

// -----------------------------------------------------------------------
// fieldClass()
// -----------------------------------------------------------------------

it('maps boolean to Toggle', function () {
    expect($this->mapper->fieldClass('is_active', 'boolean'))->toBe('Toggle');
});

it('maps date to DatePicker', function () {
    expect($this->mapper->fieldClass('published_at', 'date'))->toBe('DatePicker');
});

it('maps text/longtext to RichEditor', function (string $type) {
    expect($this->mapper->fieldClass('body', $type))->toBe('RichEditor');
})->with(['text', 'longtext', 'mediumtext']);

it('maps image column field name to FileUpload', function () {
    expect($this->mapper->fieldClass('image', 'varchar'))->toBe('FileUpload');
    expect($this->mapper->fieldClass('avatar', 'varchar'))->toBe('FileUpload');
});

it('maps integer to TextInput (numeric)', function () {
    expect($this->mapper->fieldClass('count', 'int'))->toBe('TextInput');
});

it('maps varchar to TextInput', function () {
    expect($this->mapper->fieldClass('name', 'varchar'))->toBe('TextInput');
});

// -----------------------------------------------------------------------
// fieldChain()
// -----------------------------------------------------------------------

it('adds ->required() to fieldChain when required is true', function () {
    $chain = $this->mapper->fieldChain('name', 'varchar', true);
    expect($chain)->toContain('->required()');
});

it('does not add ->required() when required is false', function () {
    $chain = $this->mapper->fieldChain('name', 'varchar', false);
    expect($chain)->not->toContain('->required()');
});

it('adds ->numeric() chain for integer fields', function () {
    $chain = $this->mapper->fieldChain('count', 'int', false);
    expect($chain)->toContain('->numeric()');
});

// -----------------------------------------------------------------------
// htmlInputType()
// -----------------------------------------------------------------------

it('returns "checkbox" for boolean types', function () {
    expect($this->mapper->htmlInputType('is_active', 'boolean'))->toBe('checkbox');
});

it('returns "date" for date type', function () {
    expect($this->mapper->htmlInputType('published_at', 'date'))->toBe('date');
});

it('returns "datetime-local" for datetime/timestamp', function (string $type) {
    expect($this->mapper->htmlInputType('created_at', $type))->toBe('datetime-local');
})->with(['datetime', 'timestamp']);

it('returns "email" for email field names', function (string $field) {
    expect($this->mapper->htmlInputType($field, 'varchar'))->toBe('email');
})->with(['email', 'email_address', 'user_email']);

it('returns "number" for numeric types', function (string $type) {
    expect($this->mapper->htmlInputType('amount', $type))->toBe('number');
})->with(['int', 'integer', 'decimal', 'float', 'bigint']);

it('defaults to "text" for unknown types', function () {
    expect($this->mapper->htmlInputType('notes', 'varchar'))->toBe('text');
});
