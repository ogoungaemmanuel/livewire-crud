<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Contracts;

/**
 * Contract that all form field builder classes must satisfy.
 */
interface FieldContract
{
    /**
     * Static constructor — primary entry-point for the fluent API.
     */
    public static function make(string $name): static;

    /**
     * Return the field's name / wire:model key.
     */
    public function getName(): string;

    /**
     * Return true when the field value is required.
     */
    public function isRequired(): bool;

    /**
     * Render the full field HTML (label + input + error) as a Blade string.
     */
    public function render(): string;

    /**
     * Emit the PHP fluent-builder expression for the generated Resource stub.
     */
    public function toCode(): string;
}
