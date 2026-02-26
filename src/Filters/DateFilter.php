<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Filters;

/**
 * Date range filter — renders two date inputs (from / to).
 *
 * Usage:
 *   DateFilter::make('created_at')->label('Date Range')
 *   DateFilter::make('expires_at')->withTime()
 */
class DateFilter extends Filter
{
    protected bool    $withTime     = false;
    protected ?string $fromLabel    = 'From';
    protected ?string $toLabel      = 'To';

    public function withTime(): static            { $this->withTime  = true;    return $this; }
    public function fromLabel(string $l): static  { $this->fromLabel = $l;      return $this; }
    public function toLabel(string $l): static    { $this->toLabel   = $l;      return $this; }

    public function render(string $wirePrefix = 'filter'): string
    {
        $label    = htmlspecialchars($this->getLabel());
        $type     = $this->withTime ? 'datetime-local' : 'date';
        $fromWire = "{$wirePrefix}{$this->name}From";
        $toWire   = "{$wirePrefix}{$this->name}To";
        $idFrom   = "filter_{$this->name}_from";
        $idTo     = "filter_{$this->name}_to";

        return <<<HTML
<div class="col-auto">
    <small class="text-muted me-1">{$label}</small>
    <label for="{$idFrom}" class="visually-hidden">{$this->fromLabel}</label>
    <input wire:model.live="{$fromWire}" id="{$idFrom}" type="{$type}"
           class="form-control form-control-sm d-inline-block" style="width:auto"
           placeholder="{$this->fromLabel}">
    <span class="mx-1">–</span>
    <label for="{$idTo}" class="visually-hidden">{$this->toLabel}</label>
    <input wire:model.live="{$toWire}" id="{$idTo}" type="{$type}"
           class="form-control form-control-sm d-inline-block" style="width:auto"
           placeholder="{$this->toLabel}">
</div>
HTML;
    }

    public function toCode(): string
    {
        $chain = "DateFilter::make('{$this->name}')";
        if ($this->withTime) { $chain .= '->withTime()'; }
        if ($this->label)    { $chain .= "->label('{$this->label}')"; }
        return $chain;
    }
}
