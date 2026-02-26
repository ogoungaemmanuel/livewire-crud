<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Fields;

/**
 * DateTimePicker — combined date+time input rendered as two Bootstrap inputs
 * or as a single datetime-local input.
 *
 * DateTimePicker. Maps to HTML `datetime-local`
 * under the hood, which Livewire syncs as a string (`Y-m-d\TH:i`).
 *
 * Usage:
 *   DateTimePicker::make('published_at')
 *   DateTimePicker::make('scheduled_at')->withSecond()->minDate('today')
 */
class DateTimePicker extends Field
{
    protected bool    $withSeconds = false;
    protected bool    $dateOnly    = false;
    protected bool    $timeOnly    = false;
    protected ?string $minDate     = null;
    protected ?string $maxDate     = null;

    public function withSecond(bool $val = true): static  { $this->withSeconds = $val;   return $this; }
    public function dateOnly(bool $val = true): static    { $this->dateOnly    = $val;   return $this; }
    public function timeOnly(bool $val = true): static    { $this->timeOnly    = $val;   return $this; }
    public function minDate(string $date): static         { $this->minDate     = $date;  return $this; }
    public function maxDate(string $date): static         { $this->maxDate     = $date;  return $this; }

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
        $readonly  = $this->readOnly ? ' readonly' : '';
        $min       = $this->minDate ? " min=\"{$this->minDate}\"" : '';
        $max       = $this->maxDate ? " max=\"{$this->maxDate}\"" : '';

        if ($this->dateOnly) {
            $type = 'date';
        } elseif ($this->timeOnly) {
            $type = 'time';
        } else {
            $type = 'datetime-local';
        }

        $step = ($type !== 'date' && $this->withSeconds) ? ' step="1"' : '';

        return <<<HTML
<div class="{$spanClass} mb-3">
    <label for="{$id}" class="form-label">{$label}{$required}{$hint}</label>
    <input type="{$type}" wire:model="{$name}" id="{$id}"
           class="form-control @error('{$name}') is-invalid @enderror"
           {$min}{$max}{$step}{$disabled}{$readonly}>
    {$error}
</div>
HTML;
    }

    public function toCode(): string
    {
        $chain = "DateTimePicker::make('{$this->name}')";
        if ($this->dateOnly)    { $chain .= '->dateOnly()'; }
        if ($this->timeOnly)    { $chain .= '->timeOnly()'; }
        if ($this->withSeconds) { $chain .= '->withSecond()'; }
        if ($this->minDate)     { $chain .= "->minDate('{$this->minDate}')"; }
        if ($this->maxDate)     { $chain .= "->maxDate('{$this->maxDate}')"; }
        return $chain . $this->commonChain();
    }
}
