<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Fields;

/**
 * TagsInput — lets users add / remove tags (comma-separated string value).
 *
 * TagsInput. Renders a Bootstrap-styled
 * Alpine-powered tag input that stores values as a comma-separated string
 * or a JSON array via Livewire.
 *
 * Usage:
 *   TagsInput::make('tags')
 *   TagsInput::make('skills')->separator(';')->suggestions(['PHP', 'Laravel'])
 */
class TagsInput extends Field
{
    protected string $separator    = ',';
    protected array  $suggestions  = [];

    public function separator(string $sep): static        { $this->separator   = $sep;  return $this; }

    /** @param array<string> $suggestions */
    public function suggestions(array $suggestions): static
    {
        $this->suggestions = $suggestions;
        return $this;
    }

    public function render(): string
    {
        $label       = htmlspecialchars($this->getLabel());
        $required    = $this->required ? '<span class="text-danger">*</span>' : '';
        $hint        = $this->hint ? "<small class=\"text-muted ms-2\">{$this->hint}</small>" : '';
        $error       = $this->errorBlock();
        $spanClass   = $this->spanClass();
        $name        = $this->name;
        $id          = $this->id;
        $placeholder = htmlspecialchars($this->placeholder ?? 'Add a tag and press Enter…');
        $sep         = $this->separator;

        $suggestionsList = empty($this->suggestions)
            ? ''
            : '<datalist id="' . $id . '-suggestions">'
              . implode('', array_map(
                  fn ($s) => "<option value=\"{$s}\">",
                  $this->suggestions
              ))
              . '</datalist>';

        $listAttr = empty($this->suggestions) ? '' : " list=\"{$id}-suggestions\"";

        return <<<HTML
<div class="{$spanClass} mb-3">
    <label class="form-label">{$label}{$required}{$hint}</label>
    <div x-data="{
            tags: \$wire.entangle('{$name}').defer,
            input: '',
            separator: '{$sep}',
            parsedTags() { return typeof this.tags === 'string' ? this.tags.split(this.separator).filter(t => t.trim()) : (this.tags || []); },
            addTag() { const t = this.input.trim(); if (t && !this.parsedTags().includes(t)) { const arr = this.parsedTags(); arr.push(t); this.tags = arr.join(this.separator); } this.input = ''; },
            removeTag(i) { const arr = this.parsedTags(); arr.splice(i, 1); this.tags = arr.join(this.separator); }
        }"
         class="border rounded p-2 d-flex flex-wrap gap-1 @error('{$name}') border-danger @enderror" style="cursor:text" @click="\$refs.taginput.focus()">
        <template x-for="(tag, i) in parsedTags()" :key="i">
            <span class="badge text-bg-primary d-inline-flex align-items-center gap-1">
                <span x-text="tag"></span>
                <button type="button" class="btn-close btn-close-white" style="font-size:.6em" @click.stop="removeTag(i)"></button>
            </span>
        </template>
        <input x-ref="taginput" x-model="input" type="text"
               class="border-0 outline-0 flex-grow-1 bg-transparent"
               placeholder="{$placeholder}"{$listAttr}
               @keydown.enter.prevent="addTag()"
               @keydown.backspace="if(!input) removeTag(parsedTags().length - 1)">
    </div>
    {$suggestionsList}
    {$error}
</div>
HTML;
    }

    public function toCode(): string
    {
        $chain = "TagsInput::make('{$this->name}')";
        if ($this->separator !== ',')        { $chain .= "->separator('{$this->separator}')"; }
        if (!empty($this->suggestions)) {
            $list   = implode("', '", $this->suggestions);
            $chain .= "->suggestions(['{$list}'])";
        }
        return $chain . $this->commonChain();
    }
}
