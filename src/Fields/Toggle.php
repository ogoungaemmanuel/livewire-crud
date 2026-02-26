<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Fields;

/**
 * Toggle / boolean switch field.
 *
 * Usage:
 *   Toggle::make('is_active')->label('Active')->default(true)
 *   Toggle::make('is_featured')->onColor('success')->offColor('secondary')
 */
class Toggle extends Field
{
    protected string  $onColor   = 'primary';
    protected string  $offColor  = 'secondary';
    protected ?string $onIcon    = null;
    protected ?string $offIcon   = null;
    protected bool    $inline    = false;

    public function onColor(string $color): static  { $this->onColor  = $color; return $this; }
    public function offColor(string $color): static { $this->offColor = $color; return $this; }
    public function onIcon(string $icon): static    { $this->onIcon   = $icon;  return $this; }
    public function offIcon(string $icon): static   { $this->offIcon  = $icon;  return $this; }
    public function inline(): static                { $this->inline   = true;   return $this; }

    public function render(): string
    {
        $label     = htmlspecialchars($this->getLabel());
        $name      = $this->name;
        $id        = $this->id;
        $disabled  = $this->disabled ? ' disabled' : '';
        $hint      = $this->hint ? "<small class=\"text-muted ms-2\">{$this->hint}</small>" : '';
        $error     = $this->errorBlock();
        $spanClass = $this->spanClass();

        return <<<HTML
<div class="{$spanClass} mb-3">
    <div class="form-check form-switch">
        <input wire:model="{$name}" class="form-check-input @error('{$name}') is-invalid @enderror"
               type="checkbox" role="switch" id="{$id}"{$disabled}>
        <label class="form-check-label" for="{$id}">{$label}{$hint}</label>
    </div>
    {$error}
</div>
HTML;
    }

    public function toCode(): string
    {
        $chain = "Toggle::make('{$this->name}')";
        if ($this->onColor  !== 'primary')   { $chain .= "->onColor('{$this->onColor}')"; }
        if ($this->offColor !== 'secondary')  { $chain .= "->offColor('{$this->offColor}')"; }
        return $chain . $this->commonChain();
    }
}
