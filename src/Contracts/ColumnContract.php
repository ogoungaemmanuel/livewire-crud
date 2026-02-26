<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Contracts;

/**
 * Contract that all table column builder classes must satisfy.
 */
interface ColumnContract
{
    /**
     * Static constructor — primary entry-point for the fluent API.
     */
    public static function make(string $name): static;

    /**
     * Return the column's field name.
     */
    public function getName(): string;

    /**
     * Return true when this column should not be rendered.
     */
    public function isHidden(): bool;

    /**
     * Return true when this column can be searched (used by the generator).
     */
    public function isSearchable(): bool;

    /**
     * Return true when this column can be sorted (used by the generator).
     */
    public function isSortable(): bool;

    /**
     * Render the <th> header cell as an HTML string.
     */
    public function renderHeader(): string;

    /**
     * Render the <td> data cell as an HTML / Blade string.
     *
     * @param  string $rowVar  The Blade variable for the current row, e.g. '$row'
     */
    public function renderCell(string $rowVar = '$row'): string;

    /**
     * Emit the PHP fluent-builder expression for the generated Resource stub.
     */
    public function toCode(): string;
}
