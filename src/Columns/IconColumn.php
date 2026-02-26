<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Columns;

/**
 * IconColumn — renders a boolean or status value as a Bootstrap Icon.
 *
 * Inspired by FilamentPHP's IconColumn. Ideal for boolean fields, flags,
 * or any attribute that maps to a small set of distinct icon representations.
 *
 * Usage:
 *   IconColumn::make('is_featured')       // boolean — check or x
 *   IconColumn::make('is_featured')->boolean()
 *
 *   IconColumn::make('status')
 *       ->icons([
 *           'active'   => 'bi-check-circle-fill',
 *           'inactive' => 'bi-dash-circle',
 *           'pending'  => 'bi-clock',
 *       ])
 *       ->colors([
 *           'active'   => 'text-success',
 *           'inactive' => 'text-secondary',
 *           'pending'  => 'text-warning',
 *       ])
 */
class IconColumn extends Column
{
    /** @var array<string, string>  value => Bootstrap icon class */
    protected array $icons = [];

    /** @var array<string, string>  value => Bootstrap text-colour CSS class */
    protected array $colors = [];

    /** Render as a boolean: true = check-circle (success), false = x-circle (danger) */
    protected bool $boolean = false;

    protected string $size = 'fs-5';   // Bootstrap font-size utility

    public function boolean(bool $val = true): static
    {
        $this->boolean = $val;
        if ($val) {
            $this->icons  = ['1' => 'bi-check-circle-fill', '0' => 'bi-x-circle'];
            $this->colors = ['1' => 'text-success', '0' => 'text-danger'];
        }
        return $this;
    }

    /**
     * @param array<string, string> $icons  value => icon class
     */
    public function icons(array $icons): static
    {
        $this->icons = $icons;
        return $this;
    }

    /**
     * @param array<string, string> $colors  value => CSS class
     */
    public function colors(array $colors): static
    {
        $this->colors = $colors;
        return $this;
    }

    public function size(string $size): static  { $this->size = $size; return $this; }

    public function renderCell(string $rowVar = '$row'): string
    {
        $expr       = "{$rowVar}->{$this->name}";
        $alignClass = $this->align ? " text-{$this->align}" : '';

        if ($this->boolean) {
            return <<<BLADE
<td class="text-center{$alignClass}">
    @if({$expr})
        <i class="bi bi-check-circle-fill text-success {$this->size}"></i>
    @else
        <i class="bi bi-x-circle text-danger {$this->size}"></i>
    @endif
</td>
BLADE;
        }

        // Build PHP map inline for Blade
        $iconMapLines  = '';
        $colorMapLines = '';
        foreach ($this->icons as $val => $icon) {
            $iconMapLines .= "            '{$val}' => '{$icon}',\n";
        }
        foreach ($this->colors as $val => $class) {
            $colorMapLines .= "            '{$val}' => '{$class}',\n";
        }
        $defaultIcon  = 'bi-question-circle';
        $defaultColor = 'text-secondary';

        return <<<BLADE
<td class="text-center{$alignClass}">
    @php
        \$_iconMap  = [{$iconMapLines}        ];
        \$_colorMap = [{$colorMapLines}        ];
        \$_val   = (string)({$expr});
        \$_icon  = \$_iconMap[\$_val]  ?? '{$defaultIcon}';
        \$_color = \$_colorMap[\$_val] ?? '{$defaultColor}';
    @endphp
    <i class="bi {{ \$_icon }} {{ \$_color }} {$this->size}"></i>
</td>
BLADE;
    }

    public function toCode(): string
    {
        $chain = "IconColumn::make('{$this->name}')";
        if ($this->boolean) { $chain .= '->boolean()'; }
        return $chain . $this->commonChain();
    }
}
