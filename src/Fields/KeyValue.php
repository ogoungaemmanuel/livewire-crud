<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Fields;

/**
 * KeyValue — renders a dynamic key→value pair editor.
 *
 * KeyValue. Stores data as a JSON object in the
 * underlying model column (cast `$casts = ['meta' => 'array']`).
 *
 * Alpine.js drives the client-side row-add / row-remove behaviour;
 * Livewire syncs the serialised JSON via a hidden input.
 *
 * Usage:
 *   KeyValue::make('meta')
 *   KeyValue::make('settings')->keyLabel('Setting')->valueLabel('Value')
 *   KeyValue::make('headers')->reorderable()
 */
class KeyValue extends Field
{
    protected string $keyLabel     = 'Key';
    protected string $valueLabel   = 'Value';
    protected string $addLabel     = 'Add row';
    protected bool   $keyEditable  = true;
    protected bool   $reorderable  = false;

    public function keyLabel(string $label): static    { $this->keyLabel    = $label; return $this; }
    public function valueLabel(string $label): static  { $this->valueLabel  = $label; return $this; }
    public function addLabel(string $label): static    { $this->addLabel    = $label; return $this; }
    public function disableEditingKeys(): static       { $this->keyEditable = false;  return $this; }
    public function reorderable(): static              { $this->reorderable = true;   return $this; }

    public function render(): string
    {
        $label     = htmlspecialchars($this->getLabel());
        $required  = $this->required ? '<span class="text-danger">*</span>' : '';
        $hint      = $this->hint ? "<small class=\"text-muted ms-2\">{$this->hint}</small>" : '';
        $error     = $this->errorBlock();
        $spanClass = $this->spanClass();
        $name      = $this->name;
        $keyLbl    = htmlspecialchars($this->keyLabel);
        $valLbl    = htmlspecialchars($this->valueLabel);
        $addLbl    = htmlspecialchars($this->addLabel);

        return <<<HTML
<div class="{$spanClass} mb-3">
    <label class="form-label">{$label}{$required}{$hint}</label>
    <div x-data="{
            pairs: Object.entries(JSON.parse(\$wire.{$name} || '{}')).map(([k,v]) => ({key:k,value:v})),
            addRow() { this.pairs.push({key:'',value:''}); this.sync(); },
            removeRow(i) { this.pairs.splice(i,1); this.sync(); },
            sync() { const obj = {}; this.pairs.forEach(p => { if(p.key) obj[p.key]=p.value; }); \$wire.set('{$name}', JSON.stringify(obj)); }
        }">
        <table class="table table-sm table-bordered mb-2">
            <thead class="table-light">
                <tr>
                    <th>{$keyLbl}</th>
                    <th>{$valLbl}</th>
                    <th style="width:2.5rem"></th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(pair, i) in pairs" :key="i">
                    <tr>
                        <td><input x-model="pair.key"   @change="sync()" class="form-control form-control-sm" placeholder="{$keyLbl}"></td>
                        <td><input x-model="pair.value" @change="sync()" class="form-control form-control-sm" placeholder="{$valLbl}"></td>
                        <td class="text-center"><button type="button" @click="removeRow(i)" class="btn btn-sm btn-outline-danger"><i class="bi bi-dash"></i></button></td>
                    </tr>
                </template>
            </tbody>
        </table>
        <button type="button" @click="addRow()" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-plus-lg"></i> {$addLbl}
        </button>
    </div>
    {$error}
</div>
HTML;
    }

    public function toCode(): string
    {
        $chain = "KeyValue::make('{$this->name}')";
        if ($this->keyLabel !== 'Key')    { $chain .= "->keyLabel('{$this->keyLabel}')"; }
        if ($this->valueLabel !== 'Value') { $chain .= "->valueLabel('{$this->valueLabel}')"; }
        return $chain . $this->commonChain();
    }
}
