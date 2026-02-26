<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Columns;

/**
 * RelationshipColumn — displays a value from a related Eloquent model.
 *
 * Inspired by FilamentPHP's RelationshipColumn. Renders the attribute of a
 * related record accessed via a dot-notation path (e.g. `author.name`).
 *
 * Usage:
 *   RelationshipColumn::make('category.name')->label('Category')
 *   RelationshipColumn::make('user.email')->searchable()->sortable()
 *   RelationshipColumn::make('tags.name')->separator(', ')  // has-many collection
 */
class RelationshipColumn extends Column
{
    /** Separator used when the relationship is a collection. */
    protected string  $separator    = ', ';
    protected ?string $relationship = null;
    protected ?string $attribute    = null;
    protected bool    $count        = false;  // show count instead of values
    protected ?string $countLabel   = null;   // suffix for count display e.g. "items"

    protected function __construct(string $name)
    {
        parent::__construct($name);

        // Parse dot-notation: 'category.name' → relationship=category, attr=name
        if (str_contains($name, '.')) {
            [$this->relationship, $this->attribute] = explode('.', $name, 2);
        } else {
            $this->relationship = $name;
            $this->attribute    = 'id';
        }

        // Auto-derive human label from relationship name
        $this->label = str_replace('_', ' ', ucwords($this->relationship, '_'));
    }

    public function separator(string $sep): static
    {
        $this->separator = $sep;
        return $this;
    }

    /**
     * Show the count of related records instead of their values.
     */
    public function counts(string $label = 'items'): static
    {
        $this->count      = true;
        $this->countLabel = $label;
        return $this;
    }

    public function renderCell(string $rowVar = '$row'): string
    {
        $alignClass = $this->align ? " text-{$this->align}" : '';

        if ($this->count) {
            $label = $this->countLabel ?? 'items';
            $expr  = "{$rowVar}->{$this->relationship}_count ?? {$rowVar}->{$this->relationship}->count()";
            return "<td class=\"text-nowrap{$alignClass}\">{{ {$expr} }} {$label}</td>";
        }

        $rel   = $this->relationship;
        $attr  = $this->attribute;
        $sep   = htmlspecialchars($this->separator);
        $def   = $this->default ?? '—';

        return <<<BLADE
<td class="text-nowrap{$alignClass}">
    @if({$rowVar}->{$rel} instanceof \Illuminate\Support\Collection || {$rowVar}->{$rel} instanceof \Illuminate\Database\Eloquent\Collection)
        {{ {$rowVar}->{$rel}->pluck('{$attr}')->implode('{$sep}') ?: '{$def}' }}
    @elseif({$rowVar}->{$rel})
        {{ optional({$rowVar}->{$rel})->{$attr} ?? '{$def}' }}
    @else
        {$def}
    @endif
</td>
BLADE;
    }

    public function toCode(): string
    {
        $chain = "RelationshipColumn::make('{$this->name}')";
        if ($this->separator !== ', ')  { $chain .= "->separator('{$this->separator}')"; }
        if ($this->count)               { $chain .= "->counts('{$this->countLabel}')"; }
        return $chain . $this->commonChain();
    }
}
