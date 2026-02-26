<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Fields;

/**
 * Radio button group — mutually exclusive selection from a static option list.
 *
 * Radio. The underlying Livewire property should
 * be a scalar (string or int).
 *
 * Usage:
 *   Radio::make('gender')->options(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'])
 *   Radio::make('priority')->options([...])->inline()
 */
class Radio extends Field
{
    /** @var array<string, string>  value => label */
    protected array $options = [];

    /** @var array<string, string>  value => description shown under the label */
    protected array $descriptions = [];

    protected bool $inline = false;

    /** @param array<string, string> $options */
    public function options(array $options): static         { $this->options      = $options; return $this; }

    /** @param array<string, string> $descriptions */
    public function descriptions(array $descriptions): static { $this->descriptions = $descriptions; return $this; }

    /** Render options side-by-side instead of stacked. */
    public function inline(bool $inline = true): static     { $this->inline       = $inline; return $this; }

    public function render(): string
    {
        $label     = htmlspecialchars($this->getLabel());
        $required  = $this->required ? '<span class="text-danger">*</span>' : '';
        $hint      = $this->hint ? "<small class=\"text-muted ms-2\">{$this->hint}</small>" : '';
        $error     = $this->errorBlock();
        $spanClass = $this->spanClass();
        $name      = $this->name;
        $inlineClass = $this->inline ? 'form-check-inline' : '';

        $items = '';
        foreach ($this->options as $value => $display) {
            $desc = isset($this->descriptions[$value])
                ? "<div class=\"form-text mt-0\">{$this->descriptions[$value]}</div>"
                : '';
            $items .= <<<HTML
            <div class="form-check {$inlineClass}">
                <input type="radio" class="form-check-input @error('{$name}') is-invalid @enderror"
                       id="{$name}_{$value}" wire:model="{$name}" value="{$value}"
                       @if($this->disabled) disabled @endif>
                <label class="form-check-label" for="{$name}_{$value}">{$display}</label>
                {$desc}
            </div>

HTML;
        }

        return <<<HTML
<div class="{$spanClass} mb-3">
    <fieldset>
        <legend class="form-label">{$label}{$required}{$hint}</legend>
        {$items}
    </fieldset>
    {$error}
</div>
HTML;
    }

    public function toCode(): string
    {
        $chain = "Radio::make('{$this->name}')";
        if (!empty($this->options)) {
            $items = [];
            foreach ($this->options as $k => $v) { $items[] = "'{$k}' => '{$v}'"; }
            $chain .= '->options([' . implode(', ', $items) . '])';
        }
        if ($this->inline) { $chain .= '->inline()'; }
        return $chain . $this->commonChain();
    }
}
