<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Columns;

use Xslainadmin\LivewireCrud\Contracts\ColumnContract;

/**
 * Base column class fluent table column builder.
 *
 * Columns describe how a model attribute is displayed inside the generated
 * table view and power the auto-generated Resource class.
 */
abstract class Column implements ColumnContract
{
    protected string $name;
    protected ?string $label     = null;
    protected bool   $sortable   = false;
    protected bool   $searchable = false;
    protected bool   $hidden     = false;
    protected bool   $toggleable = false;
    protected ?string $tooltip   = null;
    protected ?string $icon      = null;
    protected ?string $prefix    = null;
    protected ?string $suffix    = null;
    protected ?string $default   = null;
    protected bool   $copyable   = false;
    protected ?string $wrap      = null;
    protected ?string $align     = null; // 'start' | 'center' | 'end'

    /** @var callable|null */
    protected $formatUsing = null;

    // ── Enterprise additions ──────────────────────────────────────────────
    /** @var array<string, string>  Extra HTML attributes on the <td> element */
    protected array $extraAttributes = [];
    /** Secondary descriptive text rendered below the cell value */
    protected ?string $description = null;
    /** Bootstrap font-weight class suffix e.g. 'bold', 'semibold', 'light' */
    protected ?string $weight = null;

    protected function __construct(string $name)
    {
        $this->name  = $name;
        $this->label = str_replace('_', ' ', ucwords($name, '_'));
    }

    /**
     * Factory — Column::make('status')
     */
    public static function make(string $name): static
    {
        return new static($name);
    }

    // ── Fluent setters ────────────────────────────────────────────────────

    public function label(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function sortable(bool $sortable = true): static
    {
        $this->sortable = $sortable;
        return $this;
    }

    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;
        return $this;
    }

    public function hidden(bool $hidden = true): static
    {
        $this->hidden = $hidden;
        return $this;
    }

    public function toggleable(bool $toggleable = true): static
    {
        $this->toggleable = $toggleable;
        return $this;
    }

    public function tooltip(string $tooltip): static
    {
        $this->tooltip = $tooltip;
        return $this;
    }

    public function icon(string $icon): static
    {
        $this->icon = $icon;
        return $this;
    }

    public function prefix(string $prefix): static
    {
        $this->prefix = $prefix;
        return $this;
    }

    public function suffix(string $suffix): static
    {
        $this->suffix = $suffix;
        return $this;
    }

    public function default(string $default): static
    {
        $this->default = $default;
        return $this;
    }

    public function copyable(bool $copyable = true): static
    {
        $this->copyable = $copyable;
        return $this;
    }

    public function wrap(): static
    {
        $this->wrap = 'wrap';
        return $this;
    }

    public function alignStart(): static  { $this->align = 'start';  return $this; }
    public function alignCenter(): static { $this->align = 'center'; return $this; }
    public function alignEnd(): static    { $this->align = 'end';    return $this; }

    public function formatUsing(callable $callback): static
    {
        $this->formatUsing = $callback;
        return $this;
    }

    /** Merge extra HTML attributes onto the rendered <td> element. */
    public function extraAttributes(array $attrs): static
    {
        $this->extraAttributes = array_merge($this->extraAttributes, $attrs);
        return $this;
    }

    /** Secondary description text shown below the primary cell value. */
    public function description(string $text): static
    {
        $this->description = $text;
        return $this;
    }

    /**
     * Font-weight applied to the cell value.
     * Accepts Bootstrap weight tokens: 'bold' | 'semibold' | 'normal' | 'light'
     */
    public function weight(string $weight): static
    {
        $this->weight = $weight;
        return $this;
    }

    /** Build extra-attributes HTML fragment for use in renderCell(). */
    protected function extraAttrsHtml(): string
    {
        $html = '';
        foreach ($this->extraAttributes as $key => $val) {
            $html .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars((string) $val) . '"';
        }
        return $html;
    }

    /** Wrap a cell value in optional weight / description decorators. */
    protected function decorateValue(string $value): string
    {
        $weightClass = $this->weight ? " fw-{$this->weight}" : '';
        $out = $weightClass
            ? "<span class=\"{$weightClass}\">{$value}</span>"
            : $value;
        if ($this->description !== null) {
            $out .= '<br><small class="text-muted">' . htmlspecialchars($this->description) . '</small>';
        }
        return $out;
    }

    // ── Getters ──────────────────────────────────────────────────────────

    public function getName(): string      { return $this->name; }
    public function getLabel(): string     { return $this->label ?? $this->name; }
    public function isSortable(): bool     { return $this->sortable; }
    public function isSearchable(): bool   { return $this->searchable; }
    public function isHidden(): bool       { return $this->hidden; }
    public function isToggleable(): bool   { return $this->toggleable; }
    public function getAlign(): ?string    { return $this->align; }

    // ── Rendering ────────────────────────────────────────────────────────

    /**
     * Render the table cell value as Bootstrap 5 HTML.
     */
    abstract public function renderCell(string $rowVar = '$row'): string;

    /**
     * Render the `<th>` header cell.
     */
    public function renderHeader(): string
    {
        $alignClass = $this->align ? " text-{$this->align}" : '';
        $sort       = '';

        if ($this->sortable) {
            $sort = " wire:click=\"sortBy('{$this->name}')\" style=\"cursor:pointer;\"";
        }

        $label = htmlspecialchars($this->getLabel());

        return "<th class=\"text-nowrap{$alignClass}\"{$sort}>{$label}"
            . ($this->sortable ? ' <i class="bi bi-chevron-expand text-muted small"></i>' : '')
            . "</th>";
    }

    /**
     * Return an array of meta for IDE auto-complete and code-generation consumers.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type'       => static::class,
            'name'       => $this->name,
            'label'      => $this->getLabel(),
            'sortable'   => $this->sortable,
            'searchable' => $this->searchable,
            'hidden'     => $this->hidden,
            'align'      => $this->align,
        ];
    }

    // ── Code generation (for Resource.stub) ──────────────────────────────

    /**
     * Emit the PHP fluent builder call used inside the generated Resource class.
     * Example: TextColumn::make('name')->label('Name')->searchable()->sortable()
     */
    abstract public function toCode(): string;

    /** Build the fluent chain suffixes common to all column types. */
    protected function commonChain(): string
    {
        $chain = '';
        if ($this->label !== str_replace('_', ' ', ucwords($this->name, '_'))) {
            $chain .= "->label('" . addslashes($this->label) . "')";
        }
        if ($this->searchable) { $chain .= '->searchable()'; }
        if ($this->sortable)   { $chain .= '->sortable()'; }
        if ($this->hidden)     { $chain .= '->hidden()'; }
        if ($this->toggleable) { $chain .= '->toggleable()'; }
        if ($this->copyable)   { $chain .= '->copyable()'; }
        if ($this->default)    { $chain .= "->default('" . addslashes($this->default) . "')"; }
        if ($this->prefix)     { $chain .= "->prefix('" . addslashes($this->prefix) . "')"; }
        if ($this->suffix)     { $chain .= "->suffix('" . addslashes($this->suffix) . "')"; }
        if ($this->align)      { $chain .= '->align' . ucfirst($this->align) . '()'; }
        return $chain;
    }
}
