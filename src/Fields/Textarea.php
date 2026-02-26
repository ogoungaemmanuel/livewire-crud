<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Fields;

/**
 * Textarea field — multi-line text input.
 *
 * Usage:
 *   Textarea::make('description')->rows(4)->maxLength(1000)
 */
class Textarea extends Field
{
    protected int  $rows    = 3;
    protected bool $autosize = false;
    protected ?int $maxLength = null;

    public function rows(int $rows): static        { $this->rows = $rows; return $this; }
    public function autosize(): static             { $this->autosize = true; return $this; }
    public function maxLength(int $max): static    { $this->maxLength = $max; $this->rules['max'] = "max:{$max}"; return $this; }

    public function render(): string
    {
        $label      = htmlspecialchars($this->getLabel());
        $required   = $this->required ? '<span class="text-danger">*</span>' : '';
        $name       = $this->name;
        $id         = $this->id;
        $rows       = $this->rows;
        $placeholder = $this->placeholder ? 'placeholder="' . htmlspecialchars($this->placeholder) . '"' : '';
        $max        = $this->maxLength ? " maxlength=\"{$this->maxLength}\"" : '';
        $disabled   = $this->disabled ? ' disabled' : '';
        $readOnly   = $this->readOnly ? ' readonly' : '';
        $hint       = $this->hint ? "<small class=\"text-muted ms-2\">{$this->hint}</small>" : '';
        $error      = $this->errorBlock();
        $spanClass  = $this->spanClass();
        $extraClass = $this->extraClass ? ' ' . $this->extraClass : '';

        return <<<HTML
<div class="{$spanClass} mb-3">
    <label for="{$id}" class="form-label">{$label}{$required}{$hint}</label>
    <textarea wire:model="{$name}" id="{$id}" rows="{$rows}" class="form-control{$extraClass} @error('{$name}') is-invalid @enderror" {$placeholder}{$max}{$disabled}{$readOnly}></textarea>
    {$error}
</div>
HTML;
    }

    public function toCode(): string
    {
        $chain = "Textarea::make('{$this->name}')";
        if ($this->rows !== 3)    { $chain .= "->rows({$this->rows})"; }
        if ($this->autosize)      { $chain .= '->autosize()'; }
        if ($this->maxLength)     { $chain .= "->maxLength({$this->maxLength})"; }
        return $chain . $this->commonChain();
    }
}
