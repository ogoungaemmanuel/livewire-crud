<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Contracts;

/**
 * Contract that all table filter builder classes must satisfy.
 */
interface FilterContract
{
    /**
     * Static constructor — primary entry-point for the fluent API.
     */
    public static function make(string $name): static;

    /**
     * Return the filter's field name.
     */
    public function getName(): string;

    /**
     * Return the DB column this filter operates on.
     */
    public function getColumn(): string;

    /**
     * Render the filter's HTML form control as a Blade string.
     *
     * @param  string $wirePrefix  e.g. 'filter' → wire:model="filter{Name}"
     */
    public function render(string $wirePrefix = 'filter'): string;

    /**
     * Emit the PHP fluent-builder expression for the generated Resource stub.
     */
    public function toCode(): string;
}
