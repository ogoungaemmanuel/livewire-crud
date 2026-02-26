<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Fields;

/**
 * File upload field using Livewire's WithFileUploads trait.
 *
 * Usage:
 *   FileUpload::make('avatar')->image()->disk('public')->directory('avatars')->maxSize(2048)
 *   FileUpload::make('document')->acceptedTypes('.pdf,.docx')->multiple()
 */
class FileUpload extends Field
{
    protected bool    $isImage       = false;
    protected bool    $multiple      = false;
    protected bool    $preserveFilenames = false;
    protected string  $disk          = 'public';
    protected string  $directory     = 'uploads';
    protected ?int    $maxSize       = null;   // KB
    protected ?int    $minSize       = null;   // KB
    protected ?int    $maxFiles      = null;
    protected string  $acceptedTypes = '';
    protected bool    $showPreview   = true;

    public function image(): static  { $this->isImage = true; return $this; }
    public function multiple(bool $val = true): static  { $this->multiple = $val; return $this; }
    public function preserveFilenames(): static { $this->preserveFilenames = true; return $this; }
    public function disk(string $disk): static  { $this->disk = $disk; return $this; }
    public function directory(string $dir): static  { $this->directory = $dir; return $this; }
    public function maxSize(int $kb): static { $this->maxSize = $kb; return $this; }
    public function minSize(int $kb): static { $this->minSize = $kb; return $this; }
    public function maxFiles(int $n): static  { $this->maxFiles = $n; return $this; }
    public function acceptedTypes(string $types): static { $this->acceptedTypes = $types; return $this; }
    public function disablePreview(): static { $this->showPreview = false; return $this; }

    public function render(): string
    {
        $label     = htmlspecialchars($this->getLabel());
        $name      = $this->name;
        $id        = $this->id;
        $spanClass = $this->spanClass();
        $error     = $this->errorBlock();
        $disabled  = $this->disabled ? ' disabled' : '';
        $multiple  = $this->multiple ? ' multiple' : '';
        $accept    = $this->acceptedTypes ? " accept=\"{$this->acceptedTypes}\"" : ($this->isImage ? ' accept="image/*"' : '');
        $required  = $this->required ? ' required' : '';

        $hint = $this->hint
            ? "<div class=\"form-text\">{$this->hint}</div>"
            : ($this->maxSize ? "<div class=\"form-text\">Max size: " . round($this->maxSize / 1024, 1) . " MB</div>" : '');

        // Image preview block (only for single image uploads)
        $preview = '';
        if ($this->showPreview && $this->isImage && !$this->multiple) {
            $preview = <<<HTML

    @if(\$this->{$name} && method_exists(\$this->{$name}, 'temporaryUrl'))
        <div class="mt-2">
            <img src="{{ \$this->{$name}->temporaryUrl() }}" alt="Preview" class="img-thumbnail" style="max-height:200px;">
        </div>
    @elseif(!empty(\$this->record->{$name}))
        <div class="mt-2">
            <img src="{{ Storage::disk('{$this->disk}')->url(\$this->record->{$name}) }}" alt="Current" class="img-thumbnail" style="max-height:200px;">
        </div>
    @endif
HTML;
        }

        return <<<HTML
<div class="{$spanClass} mb-3">
    <label for="{$id}" class="form-label">{$label}</label>
    <input wire:model="{$name}" id="{$id}" type="file"
           class="form-control @error('{$name}') is-invalid @enderror"{$multiple}{$accept}{$required}{$disabled}>
    {$hint}{$preview}
    {$error}
</div>
HTML;
    }

    public function toCode(): string
    {
        $chain = "FileUpload::make('{$this->name}')";
        if ($this->isImage)            { $chain .= '->image()'; }
        if ($this->multiple)           { $chain .= '->multiple()'; }
        if ($this->disk !== 'public')  { $chain .= "->disk('{$this->disk}')"; }
        if ($this->directory !== 'uploads') { $chain .= "->directory('{$this->directory}')"; }
        if ($this->maxSize !== null)   { $chain .= "->maxSize({$this->maxSize})"; }
        if ($this->acceptedTypes)      { $chain .= "->acceptedTypes('{$this->acceptedTypes}')"; }
        return $chain . $this->commonChain();
    }
}
