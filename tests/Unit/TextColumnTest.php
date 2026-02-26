<?php

declare(strict_types=1);

use Xslainadmin\LivewireCrud\Columns\TextColumn;

// -----------------------------------------------------------------------
// Fluent API / accessors
// -----------------------------------------------------------------------

it('stores the column name', function () {
    $col = TextColumn::make('title');
    expect($col->getName())->toBe('title');
});

it('is not sortable by default', function () {
    expect(TextColumn::make('title')->isSortable())->toBeFalse();
});

it('becomes sortable after ->sortable()', function () {
    expect(TextColumn::make('title')->sortable()->isSortable())->toBeTrue();
});

it('is not searchable by default', function () {
    expect(TextColumn::make('title')->isSearchable())->toBeFalse();
});

it('becomes searchable after ->searchable()', function () {
    expect(TextColumn::make('title')->searchable()->isSearchable())->toBeTrue();
});

it('is not hidden by default', function () {
    expect(TextColumn::make('title')->isHidden())->toBeFalse();
});

it('becomes hidden after ->hidden()', function () {
    expect(TextColumn::make('title')->hidden()->isHidden())->toBeTrue();
});

// -----------------------------------------------------------------------
// renderCell()
// -----------------------------------------------------------------------

it('renders a basic <td> cell', function () {
    $html = TextColumn::make('name')->renderCell('$row');
    expect($html)->toContain('<td')
                 ->toContain('$row->name');
});

it('renders a money cell', function () {
    $html = TextColumn::make('price')->money('EUR')->renderCell('$row');
    expect($html)->toContain('number_format')
                 ->toContain('EUR');
});

it('renders a limited cell', function () {
    $html = TextColumn::make('body')->limit(100)->renderCell('$row');
    expect($html)->toContain('Str::limit')
                 ->toContain('100');
});

it('renders a "since" (human diff) cell', function () {
    $html = TextColumn::make('created_at')->since()->renderCell('$row');
    expect($html)->toContain('diffForHumans');
});

it('renders a copyable cell', function () {
    $html = TextColumn::make('email')->copyable()->renderCell('$row');
    expect($html)->toContain('clipboard');
});

it('respects prefix and suffix in renderCell', function () {
    $html = TextColumn::make('price')->prefix('$')->suffix('USD')->renderCell('$row');
    expect($html)->toContain('$')
                 ->toContain('USD');
});

// -----------------------------------------------------------------------
// toCode()
// -----------------------------------------------------------------------

it('generates minimal toCode() string', function () {
    $code = TextColumn::make('name')->toCode();
    expect($code)->toBe("TextColumn::make('name')");
});

it('generates toCode() with limit', function () {
    $code = TextColumn::make('body')->limit(80)->toCode();
    expect($code)->toContain("->limit(80)");
});

it('generates toCode() with money', function () {
    $code = TextColumn::make('price')->money('GBP')->toCode();
    expect($code)->toContain("->money('GBP')");
});

it('generates toCode() with sortable and searchable', function () {
    $code = TextColumn::make('name')->sortable()->searchable()->toCode();
    expect($code)->toContain('->sortable()')
                 ->toContain('->searchable()');
});

// -----------------------------------------------------------------------
// renderHeader()
// -----------------------------------------------------------------------

it('renders a <th> header', function () {
    $html = TextColumn::make('email')->renderHeader();
    expect($html)->toContain('<th')
                 ->toContain('Email');
});

it('renders a sortable <th> with sort link', function () {
    $html = TextColumn::make('name')->sortable()->renderHeader();
    expect($html)->toContain('wire:click');
});
