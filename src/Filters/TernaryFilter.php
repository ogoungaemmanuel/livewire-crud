<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Filters;

/**
 * Ternary (Yes / No / All) toggle filter rendered as a Bootstrap 5 button group.
 *
 * Usage:
 *   TernaryFilter::make('is_featured')->trueLabel('Featured')->falseLabel('Not Featured')
 *   TernaryFilter::make('is_active')->label('Status')
 */
class TernaryFilter extends Filter
{
    protected string $trueLabel  = 'Yes';
    protected string $falseLabel = 'No';
    protected string $allLabel   = 'All';

    public function trueLabel(string $label): static  { $this->trueLabel  = $label; return $this; }
    public function falseLabel(string $label): static { $this->falseLabel = $label; return $this; }
    public function allLabel(string $label): static   { $this->allLabel   = $label; return $this; }

    public function render(string $wirePrefix = 'filter'): string
    {
        $wireModel = "{$wirePrefix}{$this->name}";
        $idBase    = "filter_{$this->name}";

        return <<<HTML
<div class="col-auto">
    <div class="btn-group btn-group-sm" role="group">
        <input type="radio" class="btn-check" wire:model.live="{$wireModel}" value=""   id="{$idBase}_all"   autocomplete="off">
        <label class="btn btn-outline-secondary" for="{$idBase}_all">{$this->allLabel}</label>

        <input type="radio" class="btn-check" wire:model.live="{$wireModel}" value="1"  id="{$idBase}_yes"   autocomplete="off">
        <label class="btn btn-outline-success"   for="{$idBase}_yes">{$this->trueLabel}</label>

        <input type="radio" class="btn-check" wire:model.live="{$wireModel}" value="0"  id="{$idBase}_no"    autocomplete="off">
        <label class="btn btn-outline-danger"    for="{$idBase}_no">{$this->falseLabel}</label>
    </div>
</div>
HTML;
    }

    public function toCode(): string
    {
        $chain = "TernaryFilter::make('{$this->name}')";
        if ($this->trueLabel  !== 'Yes')  { $chain .= "->trueLabel('{$this->trueLabel}')"; }
        if ($this->falseLabel !== 'No')   { $chain .= "->falseLabel('{$this->falseLabel}')"; }
        if ($this->label)                 { $chain .= "->label('{$this->label}')"; }
        return $chain;
    }
}
