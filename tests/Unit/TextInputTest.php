<?php

declare(strict_types=1);

use Xslainadmin\LivewireCrud\Fields\TextInput;

// -----------------------------------------------------------------------
// Fluent API / accessors
// -----------------------------------------------------------------------

it('stores the field name', function () {
    expect(TextInput::make('email')->getName())->toBe('email');
});

it('is not required by default', function () {
    expect(TextInput::make('email')->isRequired())->toBeFalse();
});

it('becomes required after ->required()', function () {
    expect(TextInput::make('email')->required()->isRequired())->toBeTrue();
});

it('sets type to "email" after ->email()', function () {
    // render() should contain type="email"
    $html = TextInput::make('email_address')->email()->render();
    expect($html)->toContain('type="email"');
});

it('sets type to "password" after ->password()', function () {
    $html = TextInput::make('password')->password()->render();
    expect($html)->toContain('type="password"');
});

it('sets type to "url" after ->url()', function () {
    $html = TextInput::make('website')->url()->render();
    expect($html)->toContain('type="url"');
});

it('sets type to "number" after ->numeric()', function () {
    $html = TextInput::make('price')->numeric()->render();
    expect($html)->toContain('type="number"');
});

it('applies maxlength attribute', function () {
    $html = TextInput::make('name')->maxLength(100)->render();
    expect($html)->toContain('maxlength="100"');
});

it('applies minlength attribute', function () {
    $html = TextInput::make('name')->minLength(3)->render();
    expect($html)->toContain('minlength="3"');
});

it('renders prefix wrapper', function () {
    $html = TextInput::make('price')->prefix('$')->render();
    expect($html)->toContain('input-group')
                 ->toContain('$');
});

it('renders suffix wrapper', function () {
    $html = TextInput::make('weight')->suffix('kg')->render();
    expect($html)->toContain('input-group')
                 ->toContain('kg');
});

it('adds autocomplete="off" when disabled', function () {
    $html = TextInput::make('secret')->disableAutocomplete()->render();
    expect($html)->toContain('autocomplete="off"');
});

// -----------------------------------------------------------------------
// render()
// -----------------------------------------------------------------------

it('renders a form-group div', function () {
    $html = TextInput::make('name')->render();
    expect($html)->toContain('<div')
                 ->toContain('<label')
                 ->toContain('<input')
                 ->toContain('wire:model');
});

it('renders a required indicator when required', function () {
    $html = TextInput::make('name')->required()->render();
    expect($html)->toContain('text-danger');
});

it('renders the label text', function () {
    $html = TextInput::make('first_name')->render();
    expect($html)->toContain('First Name');
});

// -----------------------------------------------------------------------
// toCode()
// -----------------------------------------------------------------------

it('generates minimal toCode()', function () {
    $code = TextInput::make('name')->toCode();
    expect($code)->toBe("TextInput::make('name')");
});

it('generates toCode() with ->email()', function () {
    $code = TextInput::make('email')->email()->toCode();
    expect($code)->toContain('->email()');
});

it('generates toCode() with ->maxLength()', function () {
    $code = TextInput::make('name')->maxLength(255)->toCode();
    expect($code)->toContain('->maxLength(255)');
});

it('generates toCode() with ->required()', function () {
    $code = TextInput::make('name')->required()->toCode();
    expect($code)->toContain('->required()');
});

it('generates toCode() with prefix', function () {
    $code = TextInput::make('price')->prefix('$')->toCode();
    expect($code)->toContain("->prefix('$')");
});
