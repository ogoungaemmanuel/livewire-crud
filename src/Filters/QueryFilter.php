<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Filters;

use Illuminate\Database\Eloquent\Builder;

/**
 * QueryFilter — a closure-based filter that gives full control over the
 * Eloquent query modification.
 *
 * Filter with a `query()` closure.
 * Use this when SelectFilter / DateFilter are not expressive enough.
 *
 * Usage:
 *   QueryFilter::make('recent')
 *       ->label('Created this week')
 *       ->toggle()                     // checkbox-style toggle
 *       ->query(fn (Builder $q, mixed $value) => $q->where('created_at', '>=', now()->startOfWeek()))
 *
 *   QueryFilter::make('high_value')
 *       ->label('High-value orders')
 *       ->query(fn (Builder $q) => $q->where('total', '>=', 1000))
 */
class QueryFilter extends Filter
{
    /** @var callable(Builder, mixed): Builder|null */
    protected $queryClosure = null;

    /** @var callable(Builder): Builder|null — applied when the filter is cleared */
    protected $resetClosure = null;

    protected bool   $toggle    = false;   // renders as a checkbox
    protected ?string $indicator = null;
    protected bool   $persist   = true;    // persist in URL / session

    // __ fluent __

    /**
     * Set the query closure.
     * Signature: function(Builder $query, mixed $value): Builder
     */
    public function query(callable $closure): static
    {
        $this->queryClosure = $closure;
        return $this;
    }

    /**
     * Set the reset closure (called when the filter value is empty/false).
     * Signature: function(Builder $query): Builder
     */
    public function resetQuery(callable $closure): static
    {
        $this->resetClosure = $closure;
        return $this;
    }

    /** Render as a checkbox toggle rather than an input. */
    public function toggle(bool $val = true): static { $this->toggle = $val; return $this; }

    // __ runtime query application __

    /**
     * Apply this filter to an Eloquent Builder instance.
     *
     * @param  Builder  $query
     * @param  mixed    $value  The current filter value from $activeFilters
     * @return Builder
     */
    public function applyToQuery(Builder $query, mixed $value): Builder
    {
        if ($this->queryClosure === null) {
            return $query;
        }

        $isEmpty = $value === null || $value === '' || $value === false;

        if ($isEmpty) {
            if ($this->resetClosure !== null) {
                return ($this->resetClosure)($query);
            }
            return $query;
        }

        return ($this->queryClosure)($query, $value);
    }

    // __ rendering __

    public function render(string $wirePrefix = 'filter'): string
    {
        $label     = htmlspecialchars($this->getLabel());
        $wireModel = "{$wirePrefix}{$this->name}";
        $id        = "filter_{$this->name}";

        if ($this->toggle) {
            return <<<HTML
<div class="col-auto">
    <div class="form-check form-switch">
        <input type="checkbox" wire:model.live="{$wireModel}" id="{$id}" class="form-check-input" role="switch">
        <label for="{$id}" class="form-check-label small">{$label}</label>
    </div>
</div>
HTML;
        }

        return <<<HTML
<div class="col-auto">
    <label for="{$id}" class="visually-hidden">{$label}</label>
    <input type="text" wire:model.live.debounce.300ms="{$wireModel}" id="{$id}"
           class="form-control form-control-sm" placeholder="{$label}">
</div>
HTML;
    }

    public function toCode(): string
    {
        $toggle = $this->toggle ? '->toggle()' : '';
        return "QueryFilter::make('{$this->name}')" . $toggle . $this->baseChain();
    }

    protected function baseChain(): string
    {
        $chain = '';
        if ($this->label) { $chain .= "->label('{$this->label}')"; }
        return $chain;
    }
}
