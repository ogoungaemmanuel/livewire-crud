<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Fields;

/**
 * CheckboxList — renders a list of labeled checkboxes bound to an array property.
 *
 * CheckboxList. The underlying Livewire property
 * should be typed as `array` (e.g. `public array $permissions = []`).
 *
 * Usage:
 *   CheckboxList::make('permissions')
 *       ->options(['read' => 'Read', 'write' => 'Write', 'delete' => 'Delete'])
 *   CheckboxList::make('categories')->columns(2)
 */
class CheckboxList extends Field
{
    /** @var array<string, string>  value => label */
    protected array $options    = [];
    protected int   $columns    = 1;
    protected bool  $searchable = false;
    protected bool  $selectAll  = false;

    /** @param array<string, string> $options */
    public function options(array $options): static   { $this->options    = $options; return $this; }
    public function columns(int $cols): static        { $this->columns    = $cols;    return $this; }
    public function searchable(): static              { $this->searchable = true;     return $this; }
    public function bulkToggleable(): static          { $this->selectAll  = true;     return $this; }

    public function render(): string
    {
        $label     = htmlspecialchars($this->getLabel());
        $required  = $this->required ? '<span class="text-danger">*</span>' : '';
        $hint      = $this->hint ? "<small class=\"text-muted ms-2\">{$this->hint}</small>" : '';
        $error     = $this->errorBlock();
        $spanClass = $this->spanClass();
        $name      = $this->name;
        $colClass  = $this->columns > 1 ? "row row-cols-{$this->columns}" : '';

        // Build static checkbox items
        $items = '';
        foreach ($this->options as $value => $display) {
            $items .= <<<HTML
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="{$name}_{$value}"
                       wire:model="{$name}" value="{$value}">
                <label class="form-check-label" for="{$name}_{$value}">{$display}</label>
            </div>

HTML;
        }

        $selectAllHtml = $this->selectAll
            ? <<<HTML
        <div class="mb-1">
            <a href="#" class="small link-secondary" wire:click.prevent="toggleAll('{$name}')">Toggle all</a>
        </div>

HTML
            : '';

        return <<<HTML
<div class="{$spanClass} mb-3">
    <fieldset>
        <legend class="form-label">{$label}{$required}{$hint}</legend>
        {$selectAllHtml}
        <div class="{$colClass}">
            {$items}
        </div>
    </fieldset>
    {$error}
</div>
HTML;
    }

    public function toCode(): string
    {
        $chain = "CheckboxList::make('{$this->name}')";
        if (!empty($this->options)) {
            $items = [];
            foreach ($this->options as $k => $v) { $items[] = "'{$k}' => '{$v}'"; }
            $chain .= '->options([' . implode(', ', $items) . '])';
        }
        if ($this->columns > 1) { $chain .= "->columns({$this->columns})"; }
        return $chain . $this->commonChain();
    }
}
