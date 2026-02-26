<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Columns;

/**
 * Plain-text column — the most common column type.
 *
 * Usage:
 *   TextColumn::make('name')->searchable()->sortable()
 *   TextColumn::make('email')->copyable()->default('—')
 */
class TextColumn extends Column
{
    protected bool $limited      = false;
    protected int  $limitLength  = 50;
    protected bool $money        = false;
    protected ?string $currency  = 'USD';
    protected bool $numeric      = false;
    protected int  $decimalPlaces = 2;
    protected bool $since        = false; // human diff: "3 days ago"

    public function limit(int $length = 50): static
    {
        $this->limited     = true;
        $this->limitLength = $length;
        return $this;
    }

    public function money(?string $currency = 'USD'): static
    {
        $this->money    = true;
        $this->currency = $currency;
        return $this;
    }

    public function numeric(int $decimalPlaces = 2): static
    {
        $this->numeric       = true;
        $this->decimalPlaces = $decimalPlaces;
        return $this;
    }

    public function since(): static
    {
        $this->since = true;
        return $this;
    }

    public function renderCell(string $rowVar = '$row'): string
    {
        $expr = "{$rowVar}->{$this->name}";

        if ($this->since) {
            $val = "optional($expr)?->diffForHumans() ?? '{$this->default}'";
        } elseif ($this->money) {
            $val = "number_format((float)($expr), 2) . ' {$this->currency}'";
        } elseif ($this->numeric) {
            $val = "number_format((float)($expr), {$this->decimalPlaces})";
        } elseif ($this->limited) {
            $val = "\\Illuminate\\Support\\Str::limit($expr, {$this->limitLength})";
        } else {
            $val = $expr;
        }

        $prefix = $this->prefix ? '<small class="text-muted">' . htmlspecialchars($this->prefix) . '</small> ' : '';
        $suffix = $this->suffix ? ' <small class="text-muted">' . htmlspecialchars($this->suffix) . '</small>' : '';
        $alignClass = $this->align ? " text-{$this->align}" : '';

        $copy = '';
        if ($this->copyable) {
            $copy = " role=\"button\" title=\"Copy\" onclick=\"navigator.clipboard.writeText('{!! {$expr} !!}')\"";
        }

        return "<td class=\"text-nowrap{$alignClass}\"{$copy}>{$prefix}{{ {$val} ?? '{$this->default}' }}{$suffix}</td>";
    }

    public function toCode(): string
    {
        $chain = "TextColumn::make('{$this->name}')";
        if ($this->limited)  { $chain .= "->limit({$this->limitLength})"; }
        if ($this->money)    { $chain .= "->money('{$this->currency}')"; }
        if ($this->numeric)  { $chain .= "->numeric({$this->decimalPlaces})"; }
        if ($this->since)    { $chain .= '->since()'; }
        return $chain . $this->commonChain();
    }
}
