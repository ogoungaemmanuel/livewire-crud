<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Columns;

/**
 * Number column — formats numeric values with grouping, decimals, prefix/suffix.
 *
 * Usage:
 *   NumberColumn::make('price')->money('USD')->summarizeSum()
 *   NumberColumn::make('quantity')->numeric(0)->summarizeAvg()
 */
class NumberColumn extends Column
{
    protected int     $decimalPlaces = 0;
    protected bool    $money         = false;
    protected ?string $currency      = null;
    protected bool    $percentage    = false;
    protected bool    $summarizeSum  = false;
    protected bool    $summarizeAvg  = false;

    public function money(?string $currency = 'USD'): static
    {
        $this->money    = true;
        $this->currency = $currency;
        $this->decimalPlaces = 2;
        return $this;
    }

    public function numeric(int $decimalPlaces = 2): static
    {
        $this->decimalPlaces = $decimalPlaces;
        return $this;
    }

    public function percentage(): static
    {
        $this->percentage = true;
        $this->suffix     = '%';
        return $this;
    }

    public function summarizeSum(): static { $this->summarizeSum = true; return $this; }
    public function summarizeAvg(): static { $this->summarizeAvg = true; return $this; }

    public function renderCell(string $rowVar = '$row'): string
    {
        $expr    = "{$rowVar}->{$this->name}";
        $default = $this->default ?? '0';

        if ($this->money && $this->currency) {
            $formatted = "number_format((float)({$expr} ?? 0), 2) . ' ' . '{$this->currency}'";
        } else {
            $formatted = "number_format((float)({$expr} ?? 0), {$this->decimalPlaces})";
        }

        $prefix = $this->prefix ? htmlspecialchars($this->prefix) . ' ' : '';
        $suffix = '';
        if ($this->percentage) {
            $suffix = '<span class="text-muted small">%</span>';
        } elseif ($this->suffix) {
            $suffix = ' <span class="text-muted small">' . htmlspecialchars($this->suffix) . '</span>';
        }
        $alignClass = $this->align ? " text-{$this->align}" : ' text-end';

        return "<td class=\"text-nowrap{$alignClass}\">{$prefix}{{ {$formatted} }}{$suffix}</td>";
    }

    public function toCode(): string
    {
        $chain = "NumberColumn::make('{$this->name}')";
        if ($this->money)        { $chain .= "->money('" . ($this->currency ?? 'USD') . "')"; }
        if (!$this->money && $this->decimalPlaces !== 0) { $chain .= "->numeric({$this->decimalPlaces})"; }
        if ($this->percentage)   { $chain .= '->percentage()'; }
        if ($this->summarizeSum) { $chain .= '->summarizeSum()'; }
        if ($this->summarizeAvg) { $chain .= '->summarizeAvg()'; }
        return $chain . $this->commonChain();
    }
}
