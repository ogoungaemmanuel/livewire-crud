<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Columns;

/**
 * ColorColumn — renders a colour hex value as a circular / rectangular swatch.
 *
 * ColorColumn. The underlying column should store
 * a valid CSS colour value (e.g. `#3B82F6` or `rgb(59,130,246)`).
 *
 * Usage:
 *   ColorColumn::make('brand_color')
 *   ColorColumn::make('hex_color')->copyable()->size('lg')
 */
class ColorColumn extends Column
{
    /** Display size: 'sm' | 'md' | 'lg' */
    protected string $swatchSize = 'md';

    /** Show the hex value as text beside the swatch. */
    protected bool $showValue = false;

    protected bool $rounded = true;

    public function size(string $size): static
    {
        $this->swatchSize = $size;
        return $this;
    }

    public function showValue(bool $show = true): static
    {
        $this->showValue = $show;
        return $this;
    }

    public function square(): static
    {
        $this->rounded = false;
        return $this;
    }

    public function renderCell(string $rowVar = '$row'): string
    {
        $expr  = "{$rowVar}->{$this->name}";
        $dim   = match ($this->swatchSize) {
            'sm'    => '16px',
            'lg'    => '32px',
            default => '24px',
        };
        $radius    = $this->rounded ? '50%' : '4px';
        $alignClass = $this->align ? " text-{$this->align}" : '';

        $label = $this->showValue
            ? " <span class=\"ms-1 small font-monospace\">{{ {$expr} }}</span>"
            : '';

        $copy = $this->copyable
            ? " role=\"button\" title=\"Copy\" onclick=\"navigator.clipboard.writeText('{{ {$expr} }}')\""
            : '';

        return <<<BLADE
<td class="text-nowrap{$alignClass}">
    @if({$expr})
        <span class="d-inline-flex align-items-center gap-1"{$copy}>
            <span style="display:inline-block;width:{$dim};height:{$dim};border-radius:{$radius};background-color:{{ {$expr} }};border:1px solid rgba(0,0,0,.15)"></span>
            {$label}
        </span>
    @else
        <span class="text-muted">{{ '{$this->default}' ?? '—' }}</span>
    @endif
</td>
BLADE;
    }

    public function toCode(): string
    {
        $chain = "ColorColumn::make('{$this->name}')";
        if ($this->swatchSize !== 'md') { $chain .= "->size('{$this->swatchSize}')"; }
        if ($this->showValue)           { $chain .= '->showValue()'; }
        if (!$this->rounded)            { $chain .= '->square()'; }
        return $chain . $this->commonChain();
    }
}
