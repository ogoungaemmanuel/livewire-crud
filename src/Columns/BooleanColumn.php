<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Columns;

/**
 * Boolean column — renders a true/false / yes|no / 1|0 field as an icon badge.
 *
 * Usage:
 *   BooleanColumn::make('is_active')
 *   BooleanColumn::make('is_verified')->trueColor('success')->falseColor('secondary')
 */
class BooleanColumn extends Column
{
    protected string $trueIcon  = 'bi-check-circle-fill';
    protected string $falseIcon = 'bi-x-circle-fill';
    protected string $trueColor  = 'text-success';
    protected string $falseColor = 'text-danger';
    protected ?string $trueLabel  = null;
    protected ?string $falseLabel = null;

    public function trueIcon(string $icon): static   { $this->trueIcon  = $icon;  return $this; }
    public function falseIcon(string $icon): static  { $this->falseIcon = $icon;  return $this; }
    public function trueColor(string $color): static { $this->trueColor  = "text-{$color}"; return $this; }
    public function falseColor(string $color): static{ $this->falseColor = "text-{$color}"; return $this; }

    public function trueLabel(string $label): static  { $this->trueLabel  = $label; return $this; }
    public function falseLabel(string $label): static { $this->falseLabel = $label; return $this; }

    public function renderCell(string $rowVar = '$row'): string
    {
        $expr       = "{$rowVar}->{$this->name}";
        $trueIcon   = $this->trueIcon;
        $falseIcon  = $this->falseIcon;
        $trueColor  = $this->trueColor;
        $falseColor = $this->falseColor;
        $trueLabel  = $this->trueLabel  ? " {{ __('{$this->trueLabel}') }}"  : '';
        $falseLabel = $this->falseLabel ? " {{ __('{$this->falseLabel}') }}" : '';

        return <<<BLADE
<td class="text-center">
    @if({$expr})
        <i class="bi {$trueIcon} {$trueColor}"{$trueLabel}></i>
    @else
        <i class="bi {$falseIcon} {$falseColor}"{$falseLabel}></i>
    @endif
</td>
BLADE;
    }

    public function toCode(): string
    {
        $chain = "BooleanColumn::make('{$this->name}')";
        if ($this->trueIcon  !== 'bi-check-circle-fill') { $chain .= "->trueIcon('{$this->trueIcon}')"; }
        if ($this->falseIcon !== 'bi-x-circle-fill')     { $chain .= "->falseIcon('{$this->falseIcon}')"; }
        return $chain . $this->commonChain();
    }
}
