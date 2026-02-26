<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Columns;

/**
 * Badge column — renders an enum/status field as a coloured Bootstrap 5 badge.
 *
 * Usage:
 *   BadgeColumn::make('status')
 *       ->colors([
 *           'success' => 'active',
 *           'danger'  => 'inactive',
 *           'warning' => 'pending',
 *       ])
 */
class BadgeColumn extends Column
{
    /** @var array<string, string|array<string>> value => Bootstrap colour */
    protected array $colors = [
        'active'   => 'success',
        'inactive' => 'danger',
        'pending'  => 'warning',
        'draft'    => 'secondary',
    ];

    /** @var array<string, string>  value => icon class */
    protected array $icons = [];

    protected bool $dot = false;

    /**
     * @param array<string, string> $colors  ['successColour' => 'fieldValue', ...]
     *   or ['fieldValue' => 'successColour']  — both conventions accepted.
     */
    public function colors(array $colors): static
    {
        $this->colors = $colors;
        return $this;
    }

    /**
     * Map field values to Bootstrap icon classes shown beside the badge text.
     *
     * @param array<string, string> $icons  ['active' => 'bi-check-circle', ...]
     */
    public function icons(array $icons): static
    {
        $this->icons = $icons;
        return $this;
    }

    /** Show a coloured dot instead of the full badge pill. */
    public function dot(bool $dot = true): static
    {
        $this->dot = $dot;
        return $this;
    }

    public function renderCell(string $rowVar = '$row'): string
    {
        $expr = "{$rowVar}->{$this->name}";

        // Build PHP colour map for the Blade template
        $mapLines = [];
        foreach ($this->colors as $val => $colour) {
            $mapLines[] = "        '{$val}' => '{$colour}',";
        }
        $mapPhp   = implode("\n", $mapLines);

        $iconMap  = '';
        if (!empty($this->icons)) {
            $iLines = [];
            foreach ($this->icons as $val => $icon) {
                $iLines[] = "            '{$val}' => '{$icon}',";
            }
            $iconMap = '$iconMap = [' . implode('', $iLines) . ']; '
                . '$icon = $iconMap[' . $expr . '] ?? \'\'; ';
        }

        $dotHtml  = $this->dot ? '<span class="badge-dot me-1"></span>' : '';

        return <<<BLADE
<td>
    @php
        \$colorMap = [
{$mapPhp}
        ];
        \$badgeColor = \$colorMap[{$expr}] ?? 'secondary';
        {$iconMap}
    @endphp
    <span class="badge text-bg-{{ \$badgeColor }}">{$dotHtml}{{ ucfirst({$expr}) ?? '{$this->default}' }}</span>
</td>
BLADE;
    }

    public function toCode(): string
    {
        $colorsPhp = $this->_arrayToCode($this->colors);
        $chain = "BadgeColumn::make('{$this->name}')->colors({$colorsPhp})";
        if ($this->dot) { $chain .= '->dot()'; }
        return $chain . $this->commonChain();
    }

    /** @param array<string, string> $arr */
    private function _arrayToCode(array $arr): string
    {
        $items = [];
        foreach ($arr as $k => $v) {
            $items[] = "'$k' => '$v'";
        }
        return '[' . implode(', ', $items) . ']';
    }
}
