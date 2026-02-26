<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Filters;

use Xslainadmin\LivewireCrud\Contracts\FilterContract;

/**
 * Abstract base for all table filters.
 *
 * Each filter binds to a public property on the Livewire component and emits
 * HTML for the filter toolbar above the table.
 *
 * Usage (subclasses):
 *   SelectFilter::make('status')->options(['active' => 'Active', 'inactive' => 'Inactive'])
 *   DateFilter::make('created_at')->label('Registered Between')
 *   TernaryFilter::make('is_featured')
 */
abstract class Filter implements FilterContract
{
    protected string  $name;
    protected ?string $label     = null;
    protected ?string $indicator = null;    // short label shown in active-filters bar
    protected bool    $hidden    = false;
    protected ?string $column    = null;    // DB column to filter on (defaults to $name)
    protected array   $extra     = [];

    final public function __construct(string $name)
    {
        $this->name   = $name;
        $this->column = $name;
    }

    public static function make(string $name): static
    {
        return new static($name);
    }

    /* ── fluent setters ──────────────────────────────────────────────────── */

    public function label(string $label): static       { $this->label     = $label;  return $this; }
    public function indicator(string $ind): static     { $this->indicator = $ind;    return $this; }
    public function hidden(bool $val = true): static   { $this->hidden    = $val;    return $this; }
    public function column(string $col): static        { $this->column    = $col;    return $this; }

    /* ── accessors ───────────────────────────────────────────────────────── */

    public function getName(): string   { return $this->name; }
    public function getColumn(): string { return $this->column ?? $this->name; }

    protected function getLabel(): string
    {
        return $this->label ?? ucwords(str_replace(['-', '_'], ' ', $this->name));
    }

    /* ── abstract interface ──────────────────────────────────────────────── */

    /**
     * Render the filter's HTML form control for the filter toolbar.
     *
     * @param  string $wirePrefix  e.g. 'filters.' so wire:model = 'filters.status'
     */
    abstract public function render(string $wirePrefix = 'filter'): string;

    /**
     * Emit the PHP fluent-builder code for the generated Resource stub.
     */
    abstract public function toCode(): string;
}
