<?php

declare(strict_types=1);

use Xslainadmin\LivewireCrud\Fields\TextInput;
use Xslainadmin\LivewireCrud\Schema\FormSchema;

// -----------------------------------------------------------------------
// Construction / fluent API
// -----------------------------------------------------------------------

it('starts with an empty field list', function () {
    expect(FormSchema::make()->getFields())->toBeEmpty();
});

it('stores fields via ->schema()', function () {
    $schema = FormSchema::make()->schema([
        TextInput::make('name'),
        TextInput::make('email'),
    ]);
    expect($schema->getFields())->toHaveCount(2);
});

it('defaults to 2 columns', function () {
    expect(FormSchema::make()->getColumns())->toBe(2);
});

it('accepts a custom column count', function () {
    expect(FormSchema::make()->columns(3)->getColumns())->toBe(3);
});

it('clamps columns to 1 as minimum', function () {
    expect(FormSchema::make()->columns(0)->getColumns())->toBe(1);
});

it('clamps columns to 4 as maximum', function () {
    expect(FormSchema::make()->columns(10)->getColumns())->toBe(4);
});

// -----------------------------------------------------------------------
// render()
// -----------------------------------------------------------------------

it('renders a Bootstrap 5 row wrapper', function () {
    $html = FormSchema::make()->schema([
        TextInput::make('name'),
    ])->render();

    expect($html)->toContain('<div class="row g-3">');
});

it('renders each field inside the row', function () {
    $html = FormSchema::make()->schema([
        TextInput::make('name'),
        TextInput::make('email'),
    ])->render();

    expect($html)->toContain('wire:model="name"')
                 ->toContain('wire:model="email"');
});

it('renders empty row when no fields are set', function () {
    $html = FormSchema::make()->render();
    expect($html)->toContain('<div class="row g-3">');
});

// -----------------------------------------------------------------------
// toCode()
// -----------------------------------------------------------------------

it('emits each field as a code line', function () {
    $code = FormSchema::make()->schema([
        TextInput::make('name'),
        TextInput::make('email')->email(),
    ])->toCode();

    expect($code)->toContain("TextInput::make('name')")
                 ->toContain("TextInput::make('email')")
                 ->toContain('->email()');
});

it('returns empty string when schema has no fields', function () {
    expect(FormSchema::make()->toCode())->toBe('');
});
