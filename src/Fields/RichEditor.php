<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Fields;

/**
 * Rich text editor field.
 *
 * Renders a minimal Alpine.js-powered contenteditable area or a plain textarea
 * with a toolbar hint.  Projects that have Quill / Trix / TinyMCE installed can
 * wire up the generated markup to their chosen editor via the `@js` or
 * `x-init` integration pattern.
 *
 * Usage:
 *   RichEditor::make('content')->columnSpanFull()
 *   RichEditor::make('body')->toolbarButtons(['bold','italic','underline','link','bulletList'])
 */
class RichEditor extends Field
{
    protected int   $rows           = 10;
    protected array $toolbarButtons = ['bold', 'italic', 'underline', 'link', 'bulletList', 'orderedList', 'blockquote', 'code'];
    protected bool  $disableAllExtensions = false;

    /** @param list<string> $buttons */
    public function toolbarButtons(array $buttons): static
    {
        $this->toolbarButtons = $buttons;
        return $this;
    }

    public function rows(int $rows): static
    {
        $this->rows = $rows;
        return $this;
    }

    public function disableAllExtensions(): static
    {
        $this->disableAllExtensions = true;
        return $this;
    }

    public function render(): string
    {
        $label     = htmlspecialchars($this->getLabel());
        $name      = $this->name;
        $id        = $this->id;
        $spanClass = $this->spanClass();
        $error     = $this->errorBlock();
        $required  = $this->required ? ' required' : '';
        $disabled  = $this->disabled ? ' disabled' : '';
        $hint      = $this->hint ? "<div class=\"form-text\">{$this->hint}</div>" : '';

        // Build toolbar buttons HTML
        $btnMap = [
            'bold'         => ['bi-type-bold',        'bold'],
            'italic'       => ['bi-type-italic',       'italic'],
            'underline'    => ['bi-type-underline',    'underline'],
            'strikethrough'=> ['bi-type-strikethrough','strikeThrough'],
            'link'         => ['bi-link-45deg',        'createLink'],
            'bulletList'   => ['bi-list-ul',           'insertUnorderedList'],
            'orderedList'  => ['bi-list-ol',           'insertOrderedList'],
            'blockquote'   => ['bi-quote',             'formatBlock'],
            'code'         => ['bi-code',              'formatBlock'],
        ];

        $toolbarHtml = '';
        foreach ($this->toolbarButtons as $btn) {
            if (isset($btnMap[$btn])) {
                [$icon, $cmd] = $btnMap[$btn];
                $toolbarHtml .= "\n            <button type=\"button\" class=\"btn btn-sm btn-outline-secondary\" "
                    . "title=\"{$btn}\" @click.prevent=\"document.execCommand('{$cmd}', false, null)\">"
                    . "<i class=\"bi {$icon}\"></i></button>";
            }
        }

        return <<<HTML
<div class="{$spanClass} mb-3" x-data>
    <label class="form-label">{$label}</label>
    <div class="border rounded">
        <div class="border-bottom bg-light px-2 py-1 d-flex flex-wrap gap-1">{$toolbarHtml}
        </div>
        <div
            id="{$id}"
            contenteditable="true"
            class="p-2 @error('{$name}') border-danger @enderror"
            style="min-height:{$this->rows}rem; outline:none;"
            x-on:input="\$wire.set('{$name}', \$el.innerHTML)"
            x-init="\$el.innerHTML = @js(\$wire.get('{$name}'))"
            {$disabled}
        ></div>
    </div>
    <input type="hidden" wire:model="{$name}"{$required}>
    {$hint}
    {$error}
</div>
HTML;
    }

    public function toCode(): string
    {
        $chain = "RichEditor::make('{$this->name}')";
        if ($this->rows !== 10) { $chain .= "->rows({$this->rows})"; }
        if ($this->toolbarButtons !== ['bold', 'italic', 'underline', 'link', 'bulletList', 'orderedList', 'blockquote', 'code']) {
            $list = implode("', '", $this->toolbarButtons);
            $chain .= "->toolbarButtons(['{$list}'])";
        }
        return $chain . $this->commonChain();
    }
}
