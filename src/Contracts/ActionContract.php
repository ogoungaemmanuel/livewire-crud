<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Contracts;

/**
 * Contract that all action builder classes must satisfy.
 */
interface ActionContract
{
    /**
     * Static constructor — primary entry-point for the fluent API.
     */
    public static function make(string $name): static;

    /**
     * Return the action's identifier name.
     */
    public function getName(): string;

    /**
     * Return true when this action should not be rendered.
     */
    public function isHidden(): bool;

    /**
     * Render an HTML button element for this action.
     *
     * @param  string $rowVar  The Blade variable for the current row, e.g. '$row'
     */
    public function renderButton(string $rowVar = '$row'): string;

    /**
     * Emit the PHP fluent-builder expression for the generated Resource stub.
     */
    public function toCode(): string;
}
