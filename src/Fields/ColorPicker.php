<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Fields;

/**
 * ColorPicker — renders an HTML color input with optional hex value display.
 *
 * ColorPicker. The field stores a CSS hex value
 * (e.g. `#3B82F6`) in the underlying model attribute.
 *
 * Usage:
 *   ColorPicker::make('brand_color')
 *   ColorPicker::make('hex_color')->rgba()
 */
class ColorPicker extends Field
{
    protected string $format = 'hex';   // hex | rgba | hsl

    public function rgba(): static { $this->format = 'rgba'; return $this; }
    public function hsl(): static  { $this->format = 'hsl';  return $this; }

    public function render(): string
    {
        $label     = htmlspecialchars($this->getLabel());
        $required  = $this->required ? '<span class="text-danger">*</span>' : '';
        $hint      = $this->hint ? "<small class=\"text-muted ms-2\">{$this->hint}</small>" : '';
        $error     = $this->errorBlock();
        $spanClass = $this->spanClass();
        $name      = $this->name;
        $id        = $this->id;
        $disabled  = $this->disabled ? ' disabled' : '';

        return <<<HTML
<div class="{$spanClass} mb-3">
    <label for="{$id}" class="form-label">{$label}{$required}{$hint}</label>
    <div class="input-group">
        <input type="color" wire:model="{$name}" id="{$id}"
               class="form-control form-control-color @error('{$name}') is-invalid @enderror"
               title="Choose color"{$disabled}>
        <input type="text" wire:model="{$name}"
               class="form-control font-monospace @error('{$name}') is-invalid @enderror"
               placeholder="#000000" maxlength="9"{$disabled}>
    </div>
    {$error}
</div>
HTML;
    }

    public function toCode(): string
    {
        $chain = "ColorPicker::make('{$this->name}')";
        if ($this->format === 'rgba') { $chain .= '->rgba()'; }
        if ($this->format === 'hsl')  { $chain .= '->hsl()'; }
        return $chain . $this->commonChain();
    }
}
