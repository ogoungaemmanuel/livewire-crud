<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Filters;

/**
 * Dropdown select filter.
 *
 * Usage:
 *   SelectFilter::make('status')->options(['active' => 'Active', 'inactive' => 'Inactive'])
 *   SelectFilter::make('category_id')->relationship('category', 'name')
 */
class SelectFilter extends Filter
{
    protected array   $options       = [];
    protected ?string $relModel      = null;
    protected ?string $relColumn     = null;
    protected ?string $emptyLabel    = '-- All --';
    protected bool    $multiple      = false;

    public function options(array $options): static
    {
        $this->options = $options;
        return $this;
    }

    public function relationship(string $model, string $displayColumn): static
    {
        $this->relModel  = $model;
        $this->relColumn = $displayColumn;
        return $this;
    }

    public function multiple(): static { $this->multiple = true; return $this; }
    public function emptyLabel(string $label): static { $this->emptyLabel = $label; return $this; }

    public function render(string $wirePrefix = 'filter'): string
    {
        $label     = htmlspecialchars($this->getLabel());
        $wireModel = "{$wirePrefix}{$this->name}";
        $multiple  = $this->multiple ? ' multiple' : '';
        $id        = "filter_{$this->name}";

        if ($this->relModel && $this->relColumn) {
            $relVar = '$' . lcfirst($this->relModel) . 'Options';
            $options = <<<HTML

        @foreach({$relVar} as \$id => \$label)
            <option value="{{ \$id }}">{{ \$label }}</option>
        @endforeach
HTML;
        } else {
            $options = '';
            foreach ($this->options as $value => $text) {
                $options .= "\n        <option value=\"{$value}\">{$text}</option>";
            }
        }

        $emptyOpt = $this->emptyLabel ? "\n        <option value=\"\">{$this->emptyLabel}</option>" : '';

        return <<<HTML
<div class="col-auto">
    <label for="{$id}" class="visually-hidden">{$label}</label>
    <select wire:model.live="{$wireModel}" id="{$id}" class="form-select form-select-sm"{$multiple}>
        {$emptyOpt}{$options}
    </select>
</div>
HTML;
    }

    public function toCode(): string
    {
        $chain = "SelectFilter::make('{$this->name}')";
        if ($this->relModel && $this->relColumn) {
            $chain .= "->relationship('{$this->relModel}', '{$this->relColumn}')";
        } elseif ($this->options) {
            $opts  = implode(', ', array_map(
                fn ($k, $v) => "'{$k}' => '{$v}'",
                array_keys($this->options),
                $this->options,
            ));
            $chain .= "->options([{$opts}])";
        }
        if ($this->label) { $chain .= "->label('{$this->label}')"; }
        return $chain;
    }
}
