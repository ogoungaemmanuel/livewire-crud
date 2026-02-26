<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Fields;

/**
 * Date / datetime picker field.
 *
 * Usage:
 *   DatePicker::make('published_at')
 *   DatePicker::make('start_time')->withTime()
 *   DatePicker::make('birth_date')->minDate('1900-01-01')->maxDate('today')
 */
class DatePicker extends Field
{
    protected bool    $withTime = false;
    protected ?string $minDate  = null;
    protected ?string $maxDate  = null;
    protected string  $format   = 'Y-m-d';
    protected string  $displayFormat = 'M d, Y';

    public function withTime(): static
    {
        $this->withTime       = true;
        $this->format         = 'Y-m-d H:i';
        $this->displayFormat  = 'M d, Y H:i';
        return $this;
    }

    public function minDate(string $date): static { $this->minDate = $date; return $this; }
    public function maxDate(string $date): static { $this->maxDate = $date; return $this; }
    public function format(string $format): static { $this->format = $format; return $this; }

    public function render(): string
    {
        $type       = $this->withTime ? 'datetime-local' : 'date';
        $label      = htmlspecialchars($this->getLabel());
        $required   = $this->required ? '<span class="text-danger">*</span>' : '';
        $name       = $this->name;
        $id         = $this->id;
        $disabled   = $this->disabled ? ' disabled' : '';
        $readOnly   = $this->readOnly ? ' readonly' : '';
        $min        = $this->minDate ? " min=\"{$this->minDate}\"" : '';
        $max        = $this->maxDate ? " max=\"{$this->maxDate}\"" : '';
        $hint       = $this->hint ? "<small class=\"text-muted ms-2\">{$this->hint}</small>" : '';
        $error      = $this->errorBlock();
        $spanClass  = $this->spanClass();
        $extraClass = $this->extraClass ? ' ' . $this->extraClass : '';
        $req        = $this->required ? ' required' : '';

        return <<<HTML
<div class="{$spanClass} mb-3">
    <label for="{$id}" class="form-label">{$label}{$required}{$hint}</label>
    <input wire:model="{$name}" type="{$type}" id="{$id}" class="form-control{$extraClass} @error('{$name}') is-invalid @enderror"{$min}{$max}{$disabled}{$readOnly}{$req}>
    {$error}
</div>
HTML;
    }

    public function toCode(): string
    {
        $chain = "DatePicker::make('{$this->name}')";
        if ($this->withTime) { $chain .= '->withTime()'; }
        if ($this->minDate)  { $chain .= "->minDate('{$this->minDate}')"; }
        if ($this->maxDate)  { $chain .= "->maxDate('{$this->maxDate}')"; }
        return $chain . $this->commonChain();
    }
}
