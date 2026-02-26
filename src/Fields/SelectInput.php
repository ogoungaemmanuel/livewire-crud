<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Fields;

/**
 * Select / dropdown field.
 *
 * Usage:
 *   SelectInput::make('status')->options(['active' => 'Active', 'inactive' => 'Inactive'])
 *   SelectInput::make('role_id')->relationship('role', 'name')
 *   SelectInput::make('category')->native(false)  // use JS-enhanced select
 */
class SelectInput extends Field
{
    /** @var array<string, string> */
    protected array   $options      = [];
    protected bool    $multiple     = false;
    protected bool    $searchable   = false;
    protected bool    $native       = true;
    protected ?string $emptyLabel   = 'Select an option';
    protected ?string $relationship = null;
    protected ?string $titleAttribute = null;

    /** @param array<string, string> $options */
    public function options(array $options): static
    {
        $this->options = $options;
        return $this;
    }

    public function multiple(): static { $this->multiple = true; return $this; }
    public function searchable(): static { $this->searchable = true; return $this; }
    public function native(bool $native = true): static { $this->native = $native; return $this; }
    public function emptyLabel(?string $label): static { $this->emptyLabel = $label; return $this; }

    public function relationship(string $relationshipName, string $titleAttribute): static
    {
        $this->relationship    = $relationshipName;
        $this->titleAttribute  = $titleAttribute;
        return $this;
    }

    public function render(): string
    {
        $label     = htmlspecialchars($this->getLabel());
        $required  = $this->required ? '<span class="text-danger">*</span>' : '';
        $name      = $this->name;
        $id        = $this->id;
        $disabled  = $this->disabled ? ' disabled' : '';
        $multiple  = $this->multiple ? ' multiple' : '';
        $hint      = $this->hint ? "<small class=\"text-muted ms-2\">{$this->hint}</small>" : '';
        $error     = $this->errorBlock();
        $spanClass = $this->spanClass();
        $extraClass = $this->extraClass ? ' ' . $this->extraClass : '';

        // Build static <option> list
        $optionsHtml = '';
        if ($this->emptyLabel !== null) {
            $optionsHtml .= "<option value=\"\">{$this->emptyLabel}</option>\n";
        }
        foreach ($this->options as $value => $display) {
            $optionsHtml .= "<option value=\"{$value}\">{{ __('" . addslashes($display) . "') }}</option>\n";
        }

        // If using a relationship, emit a @foreach loop placeholder
        if ($this->relationship) {
            $rel   = $this->relationship;
            $attr  = $this->titleAttribute ?? 'name';
            $optionsHtml .= "@foreach(\${$rel}List as \$item)\n"
                . "<option value=\"{{ \$item->id }}\">{{ \$item->{$attr} }}</option>\n"
                . "@endforeach\n";
        }

        return <<<HTML
<div class="{$spanClass} mb-3">
    <label for="{$id}" class="form-label">{$label}{$required}{$hint}</label>
    <select wire:model="{$name}" id="{$id}" class="form-select{$extraClass} @error('{$name}') is-invalid @enderror"{$disabled}{$multiple}>
        {$optionsHtml}
    </select>
    {$error}
</div>
HTML;
    }

    public function toCode(): string
    {
        $chain      = "SelectInput::make('{$this->name}')";
        if (!empty($this->options)) {
            $items = [];
            foreach ($this->options as $k => $v) { $items[] = "'{$k}' => '{$v}'"; }
            $chain .= '->options([' . implode(', ', $items) . '])';
        }
        if ($this->relationship) { $chain .= "->relationship('{$this->relationship}', '{$this->titleAttribute}')"; }
        if ($this->multiple)     { $chain .= '->multiple()'; }
        if ($this->searchable)   { $chain .= '->searchable()'; }
        if (!$this->native)      { $chain .= '->native(false)'; }
        return $chain . $this->commonChain();
    }
}
